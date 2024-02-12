<?php

namespace Qore\App\SynapseNodes\Components\AmadeusCommand\Manager\Plugin\Filter;

use Qore\Form\Field\TreeSelect;
use Qore\Form\FormManagerInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\SynapseManager\Artificer\Service\Filter;
use Qore\SynapseManager\Artificer\Service\Filter\Equal;
use Qore\SynapseManager\Plugin\Filter\Handler as FilterHandler;
use Qore\SynapseManager\Plugin\Filter\ModelInterface;

class Handler extends FilterHandler
{
    /**
     * @inheritdoc
     */
    protected function mountServiceFilters(ModelInterface $_model): bool
    {
        $fm = $_model->getForm();

        if ($_model->getServiceCollection()->count() == 1) {
            $this->mountQueryField($_model);
        }

        foreach ($this->getAttributes($_model) as $attribute) {
            switch(true) {
                case $attribute['name'] == 'userId':
                    $this->mountUserFilterField($fm, $attribute, $_model);
                    break;
                case $attribute['type'] == Datetime::class:
                    $this->mountDatetimeRangeField($fm, $attribute, $_model);
                    break;
            }
        }

        return parent::mountServiceFilters($_model);
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
    protected function mountUserFilterField(FormManagerInterface $_fm, array $_attribute, ModelInterface $_model): void
    {
        $sm = $_model->getSynapseManager();
        $service = $_model->getLastService();

        $filters = $_model->getCurrentFilters();

        if (isset($filters[$_attribute['name']])) {
            $value = $filters[$_attribute['name']] instanceof Filter
                ? $filters[$_attribute['name']]->getTypeInstance() 
                : new Equal($filters[$_attribute['name']]);
        } else {
            $value = null;
        }

        $artificer = $sm->getServicesRepository()->findByID($service->id);
        $filters = $artificer->getFilters(null, [
            $_attribute['name'] => $value,
        ]);

        foreach ($filters as $name => $value);

        $_fm->setField(new TreeSelect($name, [
            'data' => $value,
            'label' => $_attribute['label'],
            'placeholder' => 'Выберите пользователя',
            'options' => $this->getUserFilterOptions(),
            'additional' => [
                'styles' => [ 'col' => 'col-4' ],
                'multi' => false,
            ]
        ]));
    }

    /**
     * Generate user filter options
     *
     * @return array
     */
    protected function getUserFilterOptions(): array
    {
        $result = [];
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        return $mm('SM:User')->all()->map(fn($_user) => $_user->extract(['id', 'fullname' => 'label']))->toList();
    }

}
