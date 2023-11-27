<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

use Qore\ORM\Mapper\Table\Column\Datetime;
use Qore\ORM\Mapper\Table\Column\Integer;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

class Handler implements HandlerInterface
{
    /**
     * @var DataTypeConverterInterface
     */
    protected DataTypeConverter $_converter;
    /**
     * @var SynapseServiceSubject|null;
     */
    protected ?SynapseServiceSubject $_subject;

    /** Constructor
     *
     * @param DataTypeConverterInterface $_converter
     * @param SynapseServiceSubject|null $_subject
     */
    public function __construct(DataTypeConverterInterface $_converter, ?SynapseServiceSubject $_subject)
    {
        $this->_converter = $_converter;
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
        $systemAttributes = [
            'id' => Integer::class,
            '__idparent' => Integer::class,
            '__created' => Datetime::class,
            '__updated' => Datetime::class,
            '__deleted' => Datetime::class,
        ];

        foreach ($systemAttributes as $name => $type) {
            $properties[$name] = $this->_converter->convert($type);
        }

        # - combine synapse attributes
        $attributes = $_model->getLastService()->synapse()->attributes();
        foreach ($attributes as $attribute) {
            $properties[$attribute->name] = $this->_converter->convert($attribute->type);
        }

        # - Is root handler
        if (is_null($this->_subject)) {
            $state['settings'] = [];
        } else {
            $state['toOne'] = $this->_subject->isToOne();
            $state['type'] = $state['toOne'] ? 'json' : 'multi';
        }

        return true;
    }

    /**
     * Prepare data for indexing
     *
     * @param ModelInterface $_model
     * @param SynapseServiceSubject|null $_subject
     *
     * @return bool
     */
    public function index(ModelInterface $_model) : bool
    {
        if (! $objects = $_model[Model::OBJECTS_COLLECTION]) {
            return true;
        }

        $systemAttributes = [
            'id' => Integer::class,
            '__idparent' => Integer::class,
            '__created' => Datetime::class,
            '__updated' => Datetime::class,
            '__deleted' => Datetime::class,
        ];

        $attributes = $_model->getLastService()->synapse()->attributes();

        foreach ($objects as $object) {
            foreach ($systemAttributes as $attribute => $type) {
                $object[$attribute] = isset($object[$attribute]) 
                    ? $this->_converter->convertValue($type, $object[$attribute])
                    : null;
            }

            foreach ($attributes as $attribute) {
                $object[$attribute->name] = isset($object[$attribute->name]) 
                    ? $this->_converter->convertValue($attribute->type, $object[$attribute->name])
                    : null;
            }
        }

        return true;
    }

    /**
     * Prepare filters for search
     *
     * @param ModelInterface $_model
     * @param SynapseServiceSubject|null $_subject
     *
     * @return bool
     */
    public function search(ModelInterface $_model) : bool
    {
        if (! $filters = $_model[Model::FILTERS_COLLECTION]) {
            return true;
        }

        $attributes = $_model->getLastService()->synapse()->attributes();

        $attributes = array_merge(
            [
                'id' => Integer::class,
                '__idparent' => Integer::class,
                '__created' => Datetime::class,
                '__updated' => Datetime::class,
                '__deleted' => Datetime::class,
            ],
            $attributes->reduce(
                fn ($_result, $_attr) => array_merge($_result, [$_attr->name => $_attr->type]),
                []
            )
        );

        foreach ($filters as $filter) {
            foreach ($attributes as $attribute => $type) {
                isset($filter[$attribute]) && $this->prepareFilter($type, $attribute, $filter);
            }
        }

        return true;
    }

    /**
     * Prepare filter value
     *
     * @param string $type
     * @param string $_attribute
     * @param $_filter
     * 
     * @return void
     */
    protected function prepareFilter(string $_type, string $_attribute, $_filter): void 
    {
        switch(true) {
            case $_type == Datetime::class:
                $this->prepareDatetimeFilter($_type, $_attribute, $_filter);
                break;
            default:
                $this->prepareDefaultFilter($_type, $_attribute, $_filter);
                break;
        }
    }

    /**
     * Prepare value for default filter
     *
     * @param string $type
     * @param string $_attribute
     * @param $_filter
     *
     * @return void
     */
    protected function prepareDefaultFilter(string $_type, string $_attribute, $_filter): void 
    {
        $_filter[$_attribute]->apply(function($_value) use ($_type, $_attribute, $_filter) {
            $_filter[$_attribute] = ['=', $this->_converter->convertValue($_type, $_value)];
        });
    }

    /**
     * Prepare value for dateitme filter
     *
     * @param string $_type 
     * @param string $_attribute 
     * @param $_filter 
     *
     * @return void
     */
    protected function prepareDatetimeFilter(string $_type, string $_attribute, $_filter): void 
    {
        $_filter[$_attribute]->apply(function($_value) use ($_type, $_attribute, $_filter) {
            # - if filter value is bad
            if (! is_array($_value) || is_null($_value)) {
                unset($_filter[$_attribute]);
                return;
            }
            # - if set only upper limit of the range
            if (is_null($_value[0])) {
                $_filter[$_attribute] = ['<=', $this->_converter->convertValue($_type, $_value[1])];
            # - if set only lower limit of the range
            } elseif (is_null($_value[1])) {
                $_filter[$_attribute] = ['>=', $this->_converter->convertValue($_type, $_value[0])];
            # - if set lower and upper limit of the range
            } elseif (! is_null($_value[0]) && ! is_null($_value[1])) {
                $_filter[$_attribute] = [
                    'range', 
                    [
                        $this->_converter->convertValue($_type, $_value[0]),
                        $this->_converter->convertValue($_type, $_value[1]),
                    ]
                ];
            # - elsewhere skip 
            } else {
                unset($_filter[$_attribute]);
            }
        });
    }
    
}
