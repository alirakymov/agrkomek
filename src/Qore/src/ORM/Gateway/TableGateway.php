<?php

declare(strict_types=1);

namespace Qore\ORM\Gateway;

use DateTime;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\SqlInterface;
use Laminas\Db\TableGateway\TableGateway as ZendTableGateway;

class TableGateway extends ZendTableGateway
{
    /**
     * Insert
     *
     * @param  array $set
     * @return int
     */
    public function insert($_set, array $_onConflict = null)
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }

        $insert = $this->sql->insert();
        $insert->values($_set, $insert::VALUES_MULTI);

        if ($_onConflict) {
            $insert->onConflict($_onConflict);
        }

        return $this->executeInsert($insert);
    }

    /**
     * @param Where|Closure|string|array $where
     * @return int
     */
    public function softDelete($_where)
    {
        return parent::update([
            '__deleted' => (new DateTime())->format('Y.m.d H:i:s')
        ], $_where);
    }
    
    /**
     * truncate
     *
     */
    public function truncate()
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }

        return $this->sql->prepareStatementForSqlObject($this->sql->truncate())->execute();
    }

    /**
     * buildSqlString 
     *
     * @param \Laminas\Db\Sql\SqlInterface $_sql 
     *
     * @return string 
     */
    public function buildSqlString(SqlInterface $_sql): string
    {
        return $this->sql->buildSqlString($_sql);
    }

    /**
     * @return ResultSetInterface
     * @throws Exception\RuntimeException
     */
    protected function executeSelect(Select $select)
    {
        return parent::executeSelect($select);
    }

}
