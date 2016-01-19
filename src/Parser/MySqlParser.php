<?php

namespace QueryParser\Parser;

use QueryParser\ParserRequestAbstract;
use DB;

class MySqlParser extends ParserRequestAbstract
{

    /**
     * @param $field
     * @return string
     */
    protected function addAliasField($field)
    {
        return $this->table.'.'.$field;
    }

    protected function setColumnsNames()
    {
        $connection = DB::connection();
        $this->columnNames = $connection->getSchemaBuilder()->getColumnListing($this->model->getTable());
    }

}
