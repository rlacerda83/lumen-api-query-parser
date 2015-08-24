<?php

namespace QueryParser;

class QueryParserException extends \LogicException
{
    public $fields;
    
    public function __construct(array $fields, $code = 0, \Exception $previous = null)
    {
        $message = sprintf("Query parser errors on fields: %s ", implode(', ', $fields));
        $this->fields = $fields;
        parent::__construct($message, $code , $previous);
    }
}