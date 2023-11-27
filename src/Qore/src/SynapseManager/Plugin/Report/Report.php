<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Report;

use DateTime;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\Expression as PredicateExpression;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Psr\Http\Message\ResponseInterface;
use Qore\Collection\CollectionInterface;
use Qore\DealingManager\DealingManager;
use Qore\ORM\Gateway\Gateway;
use Qore\ORM\ModelManager;
use Qore\ORM\Sql\Where;
use Qore\Qore;
use Qore\QueueManager\QueueManager;
use Qore\SynapseManager\Artificer\ArtificerInterface;
use Qore\SynapseManager\Artificer\Service\Filter;
use Qore\SynapseManager\Artificer\Service\Filter\TypeInterface;
use Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;
use Qore\SynapseManager\SynapseManager;
use Qore\UploadManager\UploadManager;

class Report implements PluginInterface
{
    /**
     * @var SynapseManager
     */
    private SynapseManager $sm;

    /**
     * @var ServiceArtificerInterface
     */ private ServiceArtificerInterface $artificer;

    /**
     * @var array
     */
    private $serviceNamespaces;

    /**
     * @var array
     */
    private $configs;

    /**
     * @var DealingManager
     */
    private $dm;

    /**
     * @var QueueManager 
     */
    private $qm;

    /**
     * @var array
     */
    private $_configs;

    /**
     * @var \Qore\ORM\ModelManager
     */
    private ModelManager $mm;

    /**
     * @var \Qore\UploadManager\UploadManager
     */
    private UploadManager $um;

    /**
     * Constructor
     *
     * @param \Qore\DealingManager\DealingManager $_dm
     * @param array $_serviceNamespaces
     * @param array $_configs
     */
    public function __construct(
        DealingManager $_dm, 
        QueueManager $_qm,
        ModelManager $_mm,
        UploadManager $_um,
        array $_serviceNamespaces, 
        array $_configs
    ) {
        $this->dm = $_dm;
        $this->qm = $_qm;
        $this->mm = $_mm;
        $this->um = $_um;
        $this->serviceNamespaces = $_serviceNamespaces;
        $this->configs = $_configs;
    }

    /**
     * Set artificer instance
     *
     * @param \Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface $_artificer
     *
     * @return void
     */
    public function setArtificer(ArtificerInterface $_artificer) : void
    {
        $this->artificer = $_artificer;
    }

    /**
     * Set synapse manager
     *
     * @param \Qore\SynapseManager\SynapseManager $_sm
     *
     * @return void
     */
    public function setSynapseManager(SynapseManager $_sm) : void
    {
        $this->sm = $_sm;
    }

    /**
     * Build report file
     *
     * @param \Qore\Collection\CollectionInterface $_filters
     *
     * @return filters
     */
    public function build(CollectionInterface $_filters): bool
    {
        $uploadedFile = (new UploadedFileFactory())->createUploadedFile(
            (new StreamFactory())->createStream(''),
            null,
            UPLOAD_ERR_OK,
            sprintf('report_%s.csv', (new DateTime())->format('Ymd_Hi')),
            'text/csv'
        );

        $_filters = $_filters->toList();

        $report = ($this->mm)('QSynapse:SynapsePluginReport', [
            'fileUnique' => $this->um->saveFile($uploadedFile, false),
            'iSynapseService' => $this->artificer->getEntity()->id,
            'filters' => $_filters
        ]);

        ($this->mm)($report)->save();

        $this->qm->publish(new ReportJob([
            'artificer' => $this->artificer->getNameIdentifier(),
            'report' => $report,
        ]));

        return true;
    }

    /**
     * Retrive collection or reports
     *
     * @return array 
     */
    public function getReports($apiArtificer = null): CollectionInterface
    {
        $reports = ($this->mm)('QSynapse:SynapsePluginReport')->where([
            '@this.iSynapseService' => $this->artificer->getEntity()->id
        ])->all();

        $apiArtificer ??= $this->artificer;

        foreach ($reports as $report) {
            $report['completed'] = $report['counted'] == $report['processed'];
            $report['routes'] = [
                'download' => Qore::url(
                    $apiArtificer->getRouteName('report-download'),
                    ['id' => $report['id']]
                ),
                'remove' => Qore::url(
                    $apiArtificer->getRouteName('report-remove'),
                    ['id' => $report['id']]
                )
            ];
        }

        return $reports;
    }

    /**
     * Remove report by identifier
     *
     * @return array 
     */
    public function remove($_id): bool 
    {
        $report = ($this->mm)('QSynapse:SynapsePluginReport')->where([
            '@this.id' => $_id 
        ])->one();

        ! is_null($report) && file_exists($report->file()->getPath()) && unlink($report->file()->getPath());

        return ! is_null($report) && ($this->mm)($report)->delete();
    }

    /**
     * Download builded report
     *
     * @param $_id
     *
     * @return \Psr\Http\Message\ResponseInterface 
     */
    public function download($_id): ResponseInterface
    {
        $report = ($this->mm)('QSynapse:SynapsePluginReport')->where([
            '@this.id' => $_id 
        ])->one();

        return ! is_null($report) ? new EmptyResponse(200, [
            'X-Accel-Redirect' => $report->file()->getUri(),
            'Content-Type' => 'text/csv',
            'Content-Disposition' => sprintf('attachment; filename="%s_%s_%s.csv"', $report['id'], $report['counted'], (new DateTime())->format('Ymd_Hi')),
        ]) : new EmptyResponse(404);
    }

    /**
     * Make report
     *
     * @param array $_task 
     *
     * @return void 
     */
    public function make(array $_task): bool
    {
        $map = $this->getMap();
        $flatMap = $this->mapToFlat($map);

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        # - Get connection instance
        $connection = $mm->getAdapter()->getDriver()->getConnection();
        $connection->connect();

        /** @var SynapsePluginReport */
        $report = $_task['report'];

        # - Update report from database
        $report = $mm('QSynapse:SynapsePluginReport')->where(['@this.id' => $report['id']])->one();

        if (is_null($report)) {
            return true;
        }

        $file = fopen($report->file()->getPath(), 'a');

        $idLast = (int)$report['idLast'];
        if (! $idLast) {
            fputcsv($file, $flatMap);
        }

        $filters = Qore::collection($report['filters']);

        $gw = $this->artificer->getLocalGateway();

        if (! (int)$report['counted']) {
            $select = $this->applyFilters(clone $gw, $filters)
                ->select(fn($_select) => $_select->columns(['@this.id' => 'id'], true, false))
                ->buildSelect();

            $count = $this->artificer->mm()->select(function($_select) {
                $_select->columns(['@this.count' => new Expression('count(*)')])
                    ->limit(1);
            })->where(fn($_where) => $_where->in('@this.id', $select))->all()->extract('count')->first();

            if (! $count) {
                return true;
            }

            $report['counted'] = $count;
        }

        $chunksCount = 500;

        $i = 0;
        while (true) {

            $objects = $this->applyFilters((clone $gw)->select(function($_select) use ($i, $chunksCount) {
                $_select
                    ->limit($chunksCount) # - Берем по 100 элементов за раз
                    ->order('@this.id asc') # - Сортировка
                    ->group('@this.id'); # - Группировка обязательна иначе теряем связи O2M 
            }), $filters)->where(function($_where) use ($idLast) {
                $_where->greaterThan('@this.id', $idLast);
            })->all();

            if (! $objectsCount = $objects->count()) {
                break;
            }

            $idLast = $objects->last()->id;

            $objects = (clone $gw)
                ->select(fn ($_select) => $_select->order('@this.id asc'))
                ->where(function ($_where) use ($objects) {
                    $_where(['@this.id' => $objects->extract('id')->toList()]);
                })->all();

            $combined = $this->combine($map, $objects, $this->prepareObjects($objects));

            foreach ($combined as $object) {
                $row = [];
                foreach ($flatMap as $attribute => $label) {
                    $row[] = $object[$attribute] ?? null;
                }
                fputcsv($file, $row);
            }

            $report['processed'] = (int)$report['processed'] + $objectsCount;
            $report['idLast'] = $idLast;

            $mm($report)->save();
            
            $mm->getEntityProvider()->reset();

            $i++;

            if ($i % 10 == 0) {
                // $connection->disconnect();
                sleep(2);
                // $connection->connect();
            // } elseif ($i > 200) {
            //     fclose($file);
            //     return false;
            }
        }

        fclose($file);
        $connection->disconnect();

        return true;
    }

    /**
     * Map to flat array
     *
     * @param ModelInterface $_map 
     *
     * @return array 
     */
    private function mapToFlat(ModelInterface $_map, $_path = ['@root']): array
    {
        $result = [];
        foreach ($_map('properties') as $attributeOrSubject => $labelOrMap) {
            if ($labelOrMap instanceof ModelInterface) {
                $result = array_merge(
                    $result, 
                    $this->mapToFlat($labelOrMap, array_merge($_path, [$attributeOrSubject]))
                );
            } else {
                $result[sprintf('%s.%s', implode('.', $_path), $attributeOrSubject)] = $labelOrMap;
            }
        }

        return $result;
    }

    /**
     * Combine float cortages
     *
     * @param $_map - map of exporting fields
     * @param $_objects - objects collection
     * @param $_prepared - prepared data collection
     * @param $_path (optional) - path for fields naming
     *
     * @return array
     */
    private function combine($_map, $_objects, $_prepared, $_path = ['@root']): array
    {
        $result = [];
        $fields = $_map('properties');

        $cnObjects = $_prepared('objects');
        $cmObjects = $_prepared('combined');

        foreach ($_objects as $object) {
            $cmObject = $cmObjects($object['id']);
            $nestedMaps = [];
            foreach ($fields as $attribute => $labelOrMap) {
                if ($labelOrMap instanceof ModelInterface && isset($object[$attribute])) {
                    $nestedMaps[$attribute] = $labelOrMap;
                } else {
                    $cmObject[sprintf('%s.%s', implode('.', $_path), $attribute)] = $cnObjects[$object['id']][$attribute] ?? null;
                }
            }

            if ($nestedMaps) {
                $populated = [$cmObject];
                foreach ($nestedMaps as $subject => $map) {
                    $nested = $this->combine($map, $object[$subject], $_prepared['properties'][$subject], array_merge($_path, [$subject]));
                    if ($nested) {
                        $populated = $this->populate($populated, $nested);
                    }
                }
                $result = array_merge($result, $populated);
            } else {
                $result[] = $cmObject;
            }
        }

        return $result;
    }

    /**
     * Populate each object from cortege
     *
     * @param array $_cortege 
     * @param  $_data 
     *
     * @return array
     */
    private function populate(array $_cortege, $_data): array
    {
        $result = [];
        foreach ($_cortege as $row) {
            $oneRowCortege = [];
            foreach ($_data as $object) {
                $oneRowCortege[] = (clone $row)->merge($object);
            }
            $result = array_merge($result, $oneRowCortege);
        }

        return $result;
    }

    /**
     * Retrive gateway
     *
     * @param \Qore\Collection\CollectionInterface $_filters 
     *
     * @return \Qore\ORM\Gateway\Gateway
     */
    private function applyFilters(Gateway $_gw, CollectionInterface $_filters): Gateway
    {
        return $_gw->where(function($_where) use ($_filters) {
            # - Filters reduce to prepared array of filters
            $_filters = $_filters->reduce(function($_result, $_artificerFilter){
                foreach ($_artificerFilter['filters'] as $attribute => $value) {
                    $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
                }
                return $_result;
            }, []);

            if (! $_filters) {
                return;
            }

            foreach ($_filters as $param => $value) {
                # - If filter is object of Filter
                $value instanceof Filter 
                    ? $value->getTypeInstance()->apply($_where, $param)
                    : $_where([$param => $value]);
            }
        });
    }

    /**
     * Generate map of fields
     *
     * @return ModelInterface
     */
    private function getMap(): ModelInterface
    {
        $chain = $this->buildChain();

        $model = new Model();
        $model->isMapping(true);

        $dm = $this->dm;
        $dm(function($_builder) use ($chain) {
            foreach ($chain as $clause) {
                $_builder($clause);
            }
        })->launch($model);

        return $model(Model::MAPPING_STATE);
    }

    /**
     * Prepare objects for build report
     *
     * @param \Qore\Collection\CollectionInterface $_objects 
     *
     * @return ModelInterface
     */
    private function prepareObjects(CollectionInterface $_objects): ModelInterface
    {
        $chain = $this->buildChain();

        $model = new Model();
        $model->isPrepare(true)->setObjects($_objects);

        $dm = $this->dm;
        $dm(function($_builder) use ($chain) {
            foreach ($chain as $clause) {
                $_builder($clause);
            }
        })->launch($model);

        return $model(Model::MAPPING_STATE);
    }

    /**
     * Generate chain for combine mapping structure
     *
     * @return array
     */
    private function buildChain(): array
    {
        $service = $this->artificer->getEntity();

        $chain = array_merge(
            [$this->artificer->getNameIdentifier() => $this->getHandlerForService($service)],
            $this->recursiveCombineChain($service, [$this->artificer->getNameIdentifier()]),
        );

        return $chain;
    }

    /**
     * Combine chain recursively
     *
     * @param \Qore\SynapseManager\Structure\Entity\SynapseService $service
     * @param array $_serviceNames
     *
     * @return array
     */
    private function recursiveCombineChain(SynapseService $_service, array $_serviceNames, array $_usedServices = []): array
    {
        if (! $subjects = $_service['subjectsFrom']) {
            return [];
        }

        $_usedServices[] = $_service['id'];

        $return = [];
        foreach ($subjects as $subject) {
            $artificer = $this->sm->getServicesRepository()->findByID($subject->iSynapseServiceTo);
            $artificerService = $artificer->getEntity();
            if (in_array($artificerService->id, $_usedServices)) {
                continue;
            }
            $serviceNames = array_merge($_serviceNames, [$subject->getReferenceName()]);
            $return[$path = implode('.', $serviceNames)] = $this->getHandlerForService($subject, $path);
            $return = array_merge(
                $return, 
                $this->recursiveCombineChain($artificerService, $serviceNames, $_usedServices)
            );
        }

        return $return;
    }

    /**
     * Initialize chain processor object for requested synapse service
     *
     * @param \Qore\SynapseManager\Structure\Entity\SynapseService|\Qore\SynapseManager\Structure\Entity\SynapseServiceSubject $_service
     * @param string $_path (optional)
     *
     * @return ChainProcessor
     */
    private function getHandlerForService($_service, string $_path = null) : ChainProcessor
    {
        $subject = null;
        if ($_service instanceof SynapseServiceSubject) {
            $subject = $_service;
            $_service = $_service->serviceTo();
        }

        $_path ??= '@root';

        $classname = $this->findClassname($_service) ?? $this->getDefaultHandlerClassname();
        return new ChainProcessor(
            new ExecuteHandler(new $classname($subject)),
            $_service,
            $_path
        );
    }

    /**
     * Find classname for _target of SynapseService
     *
     * @param \Qore\SynapseManager\Structure\Entity\SynapseService $_service
     *
     * @return string
     */
    private function findClassname(SynapseService $_service) : ?string
    {
        $classTemplate = '%s\\%s\\%s\\Plugin\\Report\\Handler';

        foreach ($this->serviceNamespaces as $namespace) {
            $class = sprintf(
                $classTemplate,
                $namespace,
                $_service->synapse->name,
                $_service->name,
            );

            if (class_exists($class) && in_array(HandlerInterface::class, class_implements($class))) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Get default classname for handler
     *
     * @return string
     */
    private function getDefaultHandlerClassname(): string
    {
        return Handler::class;
    }

}
