<?php

namespace Qore\SynapseManager\Artificer\Service\Filter;

use DateTime as DateTimeType;
use Qore\ORM\Gateway\GatewayCursor;
use Qore\ORM\Mapper\Table\Column\Datetime;
use Qore\ORM\Mapper\Table\Column\Integer;

trait Utils
{
    protected array $types = [
        Datetime::class => '/^(?<year>\d{4})-(?<month>\d{1,2})-(?<day>\d{1,2})(\\T(?<hours>\d{1,2})\.(?<minutes>\d{1,2}))?$/',
        Integer::class => '/^\d+$/',
    ];

    /**
     * Proxy for apply method
     *
     * @param \Qore\ORM\Gateway\GatewayCursor $_where 
     * @param string $_attribute 
     *
     * @return \Qore\ORM\Gateway\GatewayCursor
     */
    public function __invoke(GatewayCursor $_where, string $_attribute): GatewayCursor
    {
        return $this->apply($_where, $_attribute);
    }

    /**
     * Convert value to string type
     *
     * @param mixed $_value 
     *
     * @return string 
     */
    protected function prepareValue($_value): string
    {
        switch(true) {
            case $_value instanceof DateTimeType:
                return $_value->format('Y-m-d\\TH.i');
            default:
                return (string)$_value;
        }
    }

    /**
     * Estimated types
     *
     * @param  $_value 
     * @param array $_estimatedTypes (optional) 
     *
     * @return
     */
    protected function parseValue($_value, array $_estimatedTypes = [])
    {
        foreach ($_estimatedTypes as $type => $parser) {
            $type = is_int($type) ? $parser : $type;
            $regex = $this->types[$type] ?? $type;

            $result = [];
            if (preg_match($regex, $_value, $result)) {
                switch(true) {
                    case is_callable($parser):
                        return $parser($result);
                    case $type == Datetime::class:
                        return $this->parseDatetime($result);
                    case $type == Integer::class:
                        return (int)$result[0];
                    default:
                        return $result[0];
                }
            }
        }

        return null;
    }

    /**
     * Parse date time from value
     *
     * @param array $_result 
     *
     * @return \DateTime|null
     */
    protected function parseDatetime(array $_parsed): ?DateTimeType
    {
        return DateTimeType::createFromFormat('Y-m-d H:i:s', vsprintf('%s-%02d-%02d %02d:%02d:00', [
            $_parsed['year'],
            (int)$_parsed['month'],
            (int)$_parsed['day'],
            (int)($_parsed['hours'] ?? 23),
            (int)($_parsed['minutes'] ?? 59),
        ]));
    }

}
