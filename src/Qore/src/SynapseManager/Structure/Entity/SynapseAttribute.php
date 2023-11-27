<?php

namespace Qore\SynapseManager\Structure\Entity;

use Qore\ORM\Entity;
use Qore\ORM\Mapper\Table\Column;

/**
 * Class: SynapseAttribute
 *
 * @see Entity\Entity
 */
class SynapseAttribute extends Entity\Entity
{

    /**
     * getTypes
     *
     */
    public static function getTypes()
    {
        return [
            Column\BigInteger::class => [
                'label' => 'Большое целое число',
                'length' => 20,
                'default' => 0
            ],
            Column\Integer::class => [
                'label' => 'Целое число',
                'length' => 11,
                'default' => '0'
            ],
            Column\Decimal::class => [
                'label' => 'Десятичное число',
                'length' => '20,2',
                'default' => '0.00'
            ],
            Column\Datetime::class => [
                'label' => 'Дата и время',
            ],
            Column\Varchar::class => [
                'label' => 'Символьное значение',
                'length' => 255,
                'null' => true,
                'default' => null,
            ],
            Column\VarcharBig::class => [
                'label' => 'Символьное длинное (1024) значение',
                'length' => 1024,
                'null' => true,
                'default' => null,
            ],
            Column\Text::class => [
                'label' => 'Текстовое значение',
                'null' => true,
                'default' => null,
            ],
            Column\LongText::class => [
                'label' => 'Большое текстовое значение',
                'null' => true,
                'default' => null,
            ],
        ];
    }

}
