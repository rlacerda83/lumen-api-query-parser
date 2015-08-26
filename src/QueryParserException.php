<?php

namespace QueryParser;

class QueryParserException extends \LogicException
{
    public $fields;

    public function __construct(array $fields, $code = 0, \Exception $previous = null)
    {
        $this->fields = $fields;
        parent::__construct(
            $this->returnAllErrors(),
            $code, 
            $previous
        );
    }
    
    public function returnAllErrors()
    {
        $message = '';
        foreach($this->fields as $typeError => $contentArray) {
            $message .= sprintf('Query parser errors on %s: %s ', $typeError, implode(', ', $contentArray));
        }
        return $message;
    }
}
