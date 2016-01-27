<?php

namespace QueryParser\Parser;

use QueryParser\ParserRequestAbstract;
use DB;

class MySqlParser extends ParserRequestAbstract
{
    protected function setColumnsNames()
    {
        $connection = DB::connection();
        foreach ($this->tables as $table) {
            $this->columnNames[$table] = $connection->getSchemaBuilder()->getColumnListing($table);
        }
    }
}
