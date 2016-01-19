<?php

namespace QueryParser;


use Illuminate\Http\Request;
use QueryParser\Parser\MongoParser;
use QueryParser\Parser\MySqlParser;

class ParserRequestFactory
{

    const CONNECTION_DRIVER_MONGODB = 'mongodb';
    const CONNECTION_DRIVER_UNDEFINED = 'undefined';
    const CONNECTION_DRIVER_MYSQL = 'mysql';

    /**
     * @param Request $request
     * @param $model
     * @param null $queryBuilder
     * @return MongoParser|MySqlParser
     */
    public static function createParser(Request $request, $model, $queryBuilder = null)
    {
        $model = ! is_object($model) ? new $model : $model;
        if (! $model->getConnectionName() || $model->getConnection()->getName() == self::CONNECTION_DRIVER_MYSQL) {
            return new MySqlParser($request, $model, $queryBuilder);
        }

        if ($model->getConnection()->getName() == self::CONNECTION_DRIVER_MONGODB) {
            return new MongoParser($request, $model, $queryBuilder);
        }
    }

}
