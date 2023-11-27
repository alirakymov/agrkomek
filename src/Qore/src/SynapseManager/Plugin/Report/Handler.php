<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Report;

use Qore\ORM\Mapper\Table\Column\BigInteger;
use Qore\ORM\Mapper\Table\Column\Datetime;
use Qore\ORM\Mapper\Table\Column\Decimal;
use Qore\ORM\Mapper\Table\Column\Integer;
use Qore\ORM\Mapper\Table\Column\LongText;
use Qore\ORM\Mapper\Table\Column\Text;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

class Handler implements HandlerInterface
{
    /**
     * @var SynapseServiceSubject|null;
     */
    protected ?SynapseServiceSubject $_subject;

    /** Constructor
     *
     * @param SynapseServiceSubject|null $_subject
     */
    public function __construct(?SynapseServiceSubject $_subject)
    {
        $this->_subject = $_subject;
    }

    /**
     * Map service data structure to index data structure
     *
     * @param ModelInterface $_model
     *
     * @return bool
     */
    public function map(ModelInterface $_model) : bool
    {
        $state = $_model(Model::MAPPING_CURSOR);
        # - create property instance
        $properties = $state('properties');
        # - combine system attributes

        $template = $_model->getLastService()->synapse()->description . ': %s';

        $systemAttributes = [
            'id'         => 'ID',
            '__idparent' => 'ID parent',
            '__created'  => 'Дата создания',
            '__updated'  => 'Дата обновления',
        ];

        foreach ($systemAttributes as $name => $label) {
            $properties[$name] = sprintf($template, $label);
        }

        # - combine synapse attributes
        $attributes = $_model->getLastService()->synapse()->attributes();
        foreach ($attributes as $attribute) {
            $properties[$attribute->name] = sprintf($template, $attribute->label);
        }

        return true;
    }

    /**
     * Prepare data for export
     *
     * @param ModelInterface $_model
     * @param SynapseServiceSubject|null $_subject
     *
     * @return bool
     */
    public function prepare(ModelInterface $_model) : bool
    {
        if (! $objects = $_model[Model::OBJECTS_COLLECTION]) {
            return true;
        }

        $state = $_model(Model::MAPPING_CURSOR);
        $convertedObjects = $state('objects');

        $systemAttributes = [
            'id' => Integer::class,
            '__idparent' => Integer::class,
            '__created' => Datetime::class,
            '__updated' => Datetime::class,
            '__deleted' => Datetime::class,
        ];

        $attributes = $_model->getLastService()->synapse()->attributes();

        foreach ($objects as $object) {
            $cobject = $convertedObjects($object['id']);
            foreach ($systemAttributes as $attribute => $type) {
                $cobject[$attribute] = isset($object[$attribute]) 
                    ? $this->convertValue($type, $object[$attribute])
                    : null;
            }

            foreach ($attributes as $attribute) {
                $cobject[$attribute->name] = isset($object[$attribute->name]) 
                    ? $this->convertValue($attribute->type, $object[$attribute->name])
                    : null;
            }
        }

        return true;
    }

    /**
     * Convert value
     *
     * @param string $_type 
     * @param  $_value 
     *
     * @return mixed 
     */
    protected function convertValue(string $_type, $_value) 
    {

        switch (true) {
            case $_type === Integer::class:
            case $_type === BigInteger::class:
                return (int)$_value;
            case $_type === Datetime::class:
                return is_object($_value) ? $_value->format('Y-m-d H:i:s') : (int)$_value;
            case in_array($_type, [Text::class, LongText::class]) && is_array($_value):
                return json_encode($_value, JSON_UNESCAPED_UNICODE);
            case $_type === Decimal::class:
                return (float)$_value;
        }

        return $_value;
    }
    
}
