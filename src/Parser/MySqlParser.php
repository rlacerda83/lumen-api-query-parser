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
        if (strpos($field, self::TABLE_DELIMITER) == 0) {
            return $this->tables[0].'.'.$field;
        }

        return str_replace(self::TABLE_DELIMITER, '.', $field);
    }

    protected function setColumnsNames()
    {
        $connection = DB::connection();
        foreach ($this->tables as $table) {
            $this->columnNames[$table] = $connection->getSchemaBuilder()->getColumnListing($table);
        }

    }

}
