<?php

namespace QueryParser\Parser;

use QueryParser\ParserRequestAbstract;
use DB;

class MongoParser extends ParserRequestAbstract
{

    protected function setColumnsNames()
    {
        $result = DB::collection($this->table)->first();
        $arrayFields = [];
        foreach ($result as $key => $value) {
            $arrayFields[] = $key;
        }

        $this->columnNames = $arrayFields;
    }

    /**
     * @param $field
     * @return mixed
     */
    protected function addAliasField($field)
    {
        return $field;
    }

}