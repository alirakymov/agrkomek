<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Form\DataSource;

use Qore\Qore;
use Qore\Collection\Collection;
use Qore\SynapseManager\SynapseManager;
use Qore\SynapseManager\Artificer;
use Qore\SynapseManager\Structure\Entity\SynapseServiceFormField;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class: RequestDataSource
 *
 * @see DataSourceInterface
 */
class RequestDataSource implements DataSourceInterface
{
    /**
     * request
     *
     * @var mixed
     */
    private $request = null;

    /**
     * namespace
     *
     * @var mixed
     */
    private $artificer = null;

    private $sm;

    /**
     * preparedDataByNamespace
     *
     * @var mixed
     */
    private $preparedDataByNamespace = [];

    /**
     * __construct
     *
     * @param RequestHandlerInterface $_request
     * @param SynapseManager $_sm
     * @param SynapseManager\Artificer\ArtificerInterface|string $_artificer
     */
    public function __construct(ServerRequestInterface $_request, SynapseManager $_sm, $_artificer)
    {
        $this->request = $_request;
        $this->sm = $_sm;
        $this->artificer = is_string($_artificer) ? $this->sm->getFormsRepository()->findByName($_artificer) : $_artificer;

        if (! is_object($this->artificer) || ! $this->artificer instanceof Artificer\Form\FormArtificerInterface) {
            throw new Artificer\ArtificerException(sprintf(
                'Base artificer must be an instance of %s, %s is given instead',
                Artificer\Form\FormArtificerInterface::class,
                is_object($this->artificer) ? get_class($this->artificer) : gettype($this->artificer)
            ));
        }
    }

    /**
     * setRequest
     *
     * @param ServerRequestInterface $_request
     */
    public function setRequest(ServerRequestInterface $_request) : RequestDataSource
    {
        $this->request = $_request;
        return $this;
    }

    /**
     * extractData
     *
     */
    public function extractData()
    {
        $this->prepareDataFromRequest();
        return new Collection($this->extractDataByArtificer($this->artificer->getNameIdentifier()));
    }

    /**
     * extractDataByArtificer
     *
     * @param string $_namespace
     * @param mixed $_referenceName
     * @param mixed $_parentEntities
     * @param mixed $_relation
     */
    protected function extractDataByArtificer(string $_namespace, $_subject = null, $_parentEntities = null, $_referenceService = null)
    {
        $entities = [];
        $namespaceParts = explode(SynapseServiceFormField::FIELDNAME_DELIMETER, $_namespace);
        $artificer = $this->sm->getFormsRepository()->findByName(array_pop($namespaceParts));

        # - Extract current namespace entities
        if (isset($this->preparedDataByNamespace[$_namespace])) {
            foreach ($this->preparedDataByNamespace[$_namespace] as $key => $entity) {
                if (! isset($entity['__iSynapseService']) || ! $entity['__iSynapseService']) {
                    # - Init author service of this synapse
                    $entity['__iSynapseService'] = $artificer->getEntity()->service->id;
                }
                $entities[$entity->id] = $entity;
            }
        }

        # - Extract next namespaces entities
        $artificerFields = $artificer->getEntity()->fields;
        foreach ($artificerFields as $field) {
            if ($field->isForm()) {
                $relatedArtificer = $this->sm->getFormsRepository()->findByID($field->iSynapseServiceSubjectForm);
                $nextNamespace = $_namespace . SynapseServiceFormField::FIELDNAME_DELIMETER . $relatedArtificer->getNameIdentifier();
                $this->extractDataByArtificer(
                    $nextNamespace,
                    $field->relatedSubject,
                    $entities,
                    $artificer->getEntity()->service->id
                );
            }
        }

        # - Links parent entities to current namespace entities
        if (! is_null($_subject) && ! is_null($_parentEntities)) {
            $referenceName = $_subject->getReferenceName();
            $isClearLinkedReference = false;

            if ($artificer->isClearSelectionForm()) {
                foreach ($_parentEntities as $parentEntity) {
                    $parentEntity->unlink($referenceName, '*');
                }
            }

            foreach ($entities as $entity) {
                if (isset($_parentEntities[$entity->_sm_reference])) {
                    $_subject->initReferenceMetadata($entity);
                    if (isset($entity['_linked_via_*']) && $artificer->isClearSelectionForm()) {
                        $isClearLinkedReference = true;
                        // [TODO: Delete after manual testing. Refactored on 130-134 lines]
                        // $_parentEntities[$entity->_sm_reference]->unlink($referenceName, '*');
                    }
                    $_parentEntities[$entity->_sm_reference]->link($referenceName, $entity);
                }
            }

            if ($isClearLinkedReference) {
                foreach ($_parentEntities as $parentEntity) {
                    if (! isset($parentEntity[$referenceName])) { continue; }
                    $parentEntity[$referenceName] = new Collection($parentEntity[$referenceName]->reject(function($_entity){
                        return ! isset($_entity['_linked_via_*']);
                    }));
                }
            }
        }

        return $entities;
    }

    /**
     * prepareDataFromRequest
     *
     */
    protected function prepareDataFromRequest()
    {
        $this->preparedDataByNamespace = [];
        $data = $this->request->getParsedBody();
        foreach ($data as $paramIndex => $paramValues) {
            if (! is_array($paramValues)) {
                continue;
            }
            $paramIndexParts = explode(SynapseServiceFormField::FIELDNAME_DELIMETER, $paramIndex);
            $attributeName = array_pop($paramIndexParts);
            $namespace = implode(SynapseServiceFormField::FIELDNAME_DELIMETER, $paramIndexParts);
            foreach ($paramValues as $key => $value) {
                if (! isset($this->preparedDataByNamespace[$namespace])) {
                    $this->preparedDataByNamespace[$namespace] = [];
                }
                if (! isset($this->preparedDataByNamespace[$namespace][$key])) {
                    $this->preparedDataByNamespace[$namespace][$key] = [];
                }
                $this->preparedDataByNamespace[$namespace][$key][$attributeName] = $value;
            }
        }

        foreach ($this->preparedDataByNamespace as $namespace => &$subjects) {
            $namespaceParts = explode(SynapseServiceFormField::FIELDNAME_DELIMETER, $namespace);
            $subjectArtificer = $this->sm->getFormsRepository()->findByName($art = array_pop($namespaceParts));
            if (is_null($subjectArtificer)) {
                continue;
            }
            foreach ($subjects as $key => $subject) {
                if ($key == '*') {
                    unset($subjects[$key]);
                    // If is collection of id items
                    if (isset($subject['id'], $subject['_sm_reference'])) {
                        if (! is_array($subject['id'])) {
                            $subject['id'] = [$subject['id']];
                        }
                        foreach ($subject['id'] as $id) {
                            if (! $this->isIdentifier($id)) {
                                continue;
                            }
                            $subjects[$id] = $subjectArtificer->mm(null, [
                                'id' => $id,
                                '_sm_reference' => $subject['_sm_reference'],
                                '_linked_via_*' => true,
                                '__keep' => false,
                            ]);
                        }
                    }
                } else {
                    $subject['id'] = $key;
                    $subject['__keep'] = false; # - не регистрировать в репозитории
                    $subjects[$key] = $subjectArtificer->mm(null, $subject);
                }
            }
        }
    }

    /**
     * isIdentifier
     *
     * @param $_id
     */
    protected function isIdentifier($_id) : bool
    {
        $_id = (string)$_id;
        return preg_match('/[0-9]+/', $_id) || preg_match('/^new:/', $_id);
    }

}
