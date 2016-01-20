<?php

namespace QueryParser\Parser;

use QueryParser\ParserRequestAbstract;
use DB;

class MongoParser extends ParserRequestAbstract
{

    protected function setColumnsNames()
    {
        $result = DB::collection($this->tables[0])->first();
        $arrayFields = [];
        foreach ($result as $key => $value) {
            $arrayFields[] = $key;
        }

        $this->columnNames[$this->tables[0]] = $arrayFields;
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