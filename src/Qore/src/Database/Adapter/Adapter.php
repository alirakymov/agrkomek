<?php

declare(strict_types=1);

namespace Qore\Database\Adapter;

use Laminas\Db\Adapter\Adapter as ZendAdapter;

class Adapter extends ZendAdapter
{
    /**
     * formatQuotes
     *
     * @param mixed $_name
     * @return void
     */
    public function formatQuotes($_name)
    {
        if (is_array($_name)) {
            $return = [];
            foreach ($_name as $key => $value) {
                $return[$key] = $this->platform->quoteIdentifier($value);
            }

            return $return;
        }

        return $this->platform->quoteIdentifier($_name);
    }

    /**
     * formatParam
     *
     * @param mixed $_name
     * @return void
     */
    public function formatParam($_name)
    {
        if (is_array($_name)) {
            $return = [];
            foreach ($_name as $key => $value) {
                $return[$key] = $this->driver->formatParameterName($value);
            }

            return $return;
        }
        return $this->driver->formatParameterName($_name);
    }

    /**
     * formatClauseIn
     *
     * @param int $_count
     * @return void
     */
    public function formatClauseIn(int $_count)
    {
        return implode(',', array_fill(0, $_count, '?'));
    }

    /**
     * transaction
     *
     * @param \Closure $_transaction
     * @return void
     */
    public function transaction(\Closure $_transaction)
    {
        $connection = $this->driver->getConnection();

        // TODO - normally try { ... } catch () { ... }
        $connection->beginTransaction();

        try {

            $_transaction($this);

        } catch(\Exception $e) {

            $connection->rollback();
            throw $e;
        }

        $connection->commit();
    }

    /**
     * groupInsert
     *
     * @param string $_query
     * @param array $_rows
     * @return void
     */
    public function groupInsert(string $_query, array $_rows)
    {
        $preparedParameters = $rows = [];

        $rowFields = array_keys(current($_rows));

        $index = 0;
        foreach ($_rows as $row) {
            $rowParameters = [];
            foreach ($rowFields as $field) {
                $preparedParameters[$field . $index] = $row[$field];
                $rowParameters[] = $this->formatParam($field . $index);
            }
            $index++;
            $rows[] = "(" . implode(",", $rowParameters) . ")";
        }

        foreach ($rowFields as &$value) {
            $value = $this->formatQuotes($value);
        }

        return [
            vsprintf($_query, [
                "(" . implode(',', $rowFields) . ")",
                implode(',', $rows),
            ]),
            $preparedParameters,
        ];
    }
}
