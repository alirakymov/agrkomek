<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Filter;

use Qore\Form\Field\Datetime as FieldDatetime;
use Qore\Form\Field\Text;
use Qore\Form\Field\TreeSelect;
use Qore\Form\FormManagerInterface;
use Qore\ORM\Mapper\Table\Column\Datetime;
use Qore\ORM\Mapper\Table\Column\Integer;
use Qore\SynapseManager\Artificer\Service\Filter;
use Qore\SynapseManager\Artificer\Service\Filter\Between;
use Qore\SynapseManager\Artificer\Service\Filter\In;
use Qore\SynapseManager\Artificer\Service\Filter\TypeInterface;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

class Handler implements HandlerInterface
{
    /**
     * @var SynapseServiceSubject|null;
     */
    protected ?SynapseServiceSubject $_subject;

    /**
     * @var bool - mount query field
     */
    protected bool $mountQueryField = true;

    /**
     * @var array - default styles
     */
    protected array $defaultStyles = [
        'col' => 'col-3',
    ];

    /** Constructor
     *
     * @param SynapseServiceSubject|null $_subject
     */
    public function __construct(?SynapseServiceSubject $_subject)
    {
        $this->_subject = $_subject;
    }

    /**
     * @inheritdoc
     */
    public function build(ModelInterface $_model) : bool
    {
        $path = explode('.', $_model['path']);
        $count = count($path);

        switch (true) {
            case $count == 1:
                return $this->mountServiceFilters($_model);
            case $count == 2:
                return $this->mountSubjectFilters($_model);
        }

        return true;
    }

    /**
     * Mount service filter
     *
     * @param ModelInterface $_model 
     *
     * @return bool 
     */
    protected function mountServiceFilters(ModelInterface $_model): bool
    {
        $fm = $_model->getForm();

        if ($_model->getServiceCollection()->count() == 1 && $this->mountQueryField) {
            $this->mountQueryField($_model);
        }

        foreach ($this->getAttributes($_model) as $attribute) {
            switch(true) {
                case $attribute['type'] == Datetime::class:
                    $this->mountDatetimeRangeField($fm, $attribute, $_model);
                    break;
            }
        }

        return true;
    }

    /**
     * Get current service attributes
     *
     * @param ModelInterface $_model 
     *
     * @return array 
     */
    protected function getAttributes(ModelInterface $_model): array
    {
        $service = $_model->getLastService();
        $attributes = $service->synapse()->attributes();

        return array_merge(
            [
                [
                    'name' => '__created',
                    'label' => 'Дата создания',
                    'type' => Datetime::class,
                ],
                [
                    'name' => '__updated',
                    'label' => 'Дата изменения',
                    'type' => Datetime::class,
                ],
            ], 
            $attributes->map(
                fn ($_attr) => [
                    'name' => $_attr['name'],
                    'label' => $_attr['label'],
                    'type' => $_attr['type'],
                ]
            )->toList()
        );
    }

    /**
     * Mount query field
     *
     * @param ModelInterface $_model 
     *
     * @return void
     */
    protected function mountQueryField(ModelInterface $_model): void
    {
        $fm = $_model->getForm();
        $queryParams = $_model->getRequest()->getQueryParams();

        $fm->setField(new Text('query', [
            'data' => $queryParams['query'] ?? '',
            'label' => 'Поиск',
            'placeholder' => 'Поиск',
            'additional' => [
                'range' => true,
                'format' => 'Y-m-dTH.i'
            ]
        ]));
    }

    /**
     * Mount subject filters
     *
     * @param ModelInterface $_model 
     *
     * @return bool 
     */
    protected function mountSubjectFilters(ModelInterface $_model): bool
    {
        $sm = $_model->getSynapseManager();
        $service = $_model->getLastService();

        $filters = $_model->getCurrentFilters();


        if (isset($filters['id'])) {
            $value = $filters['id'] instanceof Filter
                ? $filters['id']->getTypeInstance() 
                : new In($filters[$_attribute['name']]);
        } else {
            $value = new In([]);
        }
        
        $firstArtificer = $sm->getServicesRepository()->findByID($_model->getServiceCollection()->first()->id);
        $artificer = $sm->getServicesRepository()->findByID($service->id);

        $items = $artificer->mm()->all()->map(function($_item){
            $search = $replace = [];
            // foreach ($_item as $key => $value) {
            //     if (is_scalar($_item[$key])) {
            //         $search[] = '$' . $key;
            //         $replace[] = $_item[$key];
            //     }
            // }
            // $_item['title'] = str_replace($search, $replace, '$title');
            return [
                'id' => $_item['id'],
                '__idparent' => $_item['__idparent'] ?? 0,
                'label' => $_item['name'] ?? $_item['label'] ?? $_item['title'] ?: 'item: ' . $_item['id'],
            ];
        })->nest('id', '__idparent');

        $filters = $firstArtificer->getFilters($artificer, [
            'id' => $value,
        ]);

        foreach ($filters as $name => $value);

        $fm = $_model->getForm();
        $fm->setField(new TreeSelect($name, [
            'type' => TreeSelect::class,
            'label' => $service->synapse()->description,
            'placeholder' => $service->synapse()->description,
            'info' => '',
            'options' => $items,
            'additional' => [
                'styles' => $this->defaultStyles,
                'multi' => false,
                'flat' => false,
            ]
        ]));

        return true;
    }
    
    /**
     * Mount datetime range field
     *
     * @param \Qore\Form\FormManagerInterface $_fm 
     * @param array $_attribute 
     * @param ModelInterface $_model
     *
     * @return void 
     */
    protected function mountDatetimeRangeField(FormManagerInterface $_fm, array $_attribute, ModelInterface $_model): void
    {
        $sm = $_model->getSynapseManager();
        $service = $_model->getLastService();

        $filters = $_model->getCurrentFilters();

        if (isset($filters[$_attribute['name']])) {
            $value = $filters[$_attribute['name']] instanceof Filter
                ? $filters[$_attribute['name']]->getTypeInstance() 
                : new Between($filters[$_attribute['name']]);
        } else {
            $value = new Between('');
        }

        $artificer = $sm->getServicesRepository()->findByID($service->id);
        $filters = $artificer->getFilters(null, [
            $_attribute['name'] => $value,
        ]);

        foreach ($filters as $name => $value);

        $_fm->setField(new FieldDatetime($name, [
            'data' => $value,
            'label' => $_attribute['label'],
            'additional' => [
                'styles' => $this->defaultStyles,
                'range' => true,
                'format' => 'Y-m-dTH.i'
            ]
        ]));
    }

}
