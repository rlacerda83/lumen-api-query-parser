<?php

namespace QueryParser;

class Filter
{
    const TABLE_DELIMITER = '|';

    const START_OPERATOR_DELIMITER = '{';

    const END_OPERATOR_DELIMITER = '}';

    const DEFAULT_OPERATOR = '=';

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var array
     */
    protected $tables;

    /**
     * @var array
     */
    protected static $allowedOperators = [
        'eq' => '=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'like' => 'Like',
        'notin' => 'NotIn',
        'in' => 'In',
    ];

    /**
     * Filter constructor.
     * @param $rawField
     * @param array $tables
     */
    public function __construct($rawField, array $tables)
    {
        $this->tables = $tables;
        $this->extractOperator($rawField);
        $this->extractField($rawField);
    }

    /**
     * @param $field
     * @return bool
     */
    private function extractField($field)
    {
        if (strpos($field, self::TABLE_DELIMITER) == 0) {
            $field = $this->tables[0].'.'.$field;
            $this->removeOperator($field);

            return true;
        }

        $field = str_replace(self::TABLE_DELIMITER, '.', $field);
        $this->removeOperator($field);
    }

    /**
     * @param $field
     * @return bool
     */
    private function removeOperator($field)
    {
        if (strpos($field, self::START_OPERATOR_DELIMITER) == 0) {
            $this->field = $field;

            return true;
        }

        $this->field = substr($field, 0, strpos($field, self::START_OPERATOR_DELIMITER));
    }

    /**
     * @param $rawField
     * @return bool
     * @throws \Exception
     */
    private function extractOperator($rawField)
    {
        preg_match('/{(.*?)}/', $rawField, $match);

        if (! count($match)) {
            $this->operator = self::DEFAULT_OPERATOR;

            return true;
        }

        $operator = strtolower($match[1]);

        if (! isset(self::$allowedOperators[$match[1]])) {
            throw new \Exception('Operator '.$operator.' not allowed');
        }

        $this->operator = self::$allowedOperators[$match[1]];
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param $queryBuilder
     * @param $values
     */
    public function applyFilter($queryBuilder, $values)
    {
        switch ($this->getOperator()) {
            case '=':
            case '<':
            case '<=':
            case '>':
            case '>=':
                $this->applyCompareFilter($queryBuilder, $values);
                break;

            case 'Like':
                $this->applyLikeFilter($queryBuilder, $values);
                break;

            case 'NotIn':
            case 'In':
                $this->applyInFilter($queryBuilder, $values);
                break;
        }
    }

    /**
     * @param $queryBuilder
     * @param $values
     */
    private function applyCompareFilter($queryBuilder, $values)
    {
        $queryBuilder->where(function ($query) use ($values) {
            foreach ($values as $whereValue) {
                $whereValue = trim($whereValue);
                $query->orWhere($this->getField(), $this->getOperator(), $whereValue);
            }
        });
    }

    /**
     * @param $queryBuilder
     * @param $values
     */
    private function applyLikeFilter($queryBuilder, $values)
    {
        $queryBuilder->where(function ($query) use ($values) {
            foreach ($values as $whereValue) {
                $whereValue = trim($whereValue);
                $query->orWhere($this->getField(), 'LIKE', "%{$whereValue}%");
            }
        });
    }

    /**
     * @param $queryBuilder
     * @param $values
     */
    private function applyInFilter($queryBuilder, $values)
    {
        $operator = 'where'.$this->getOperator();
        $queryBuilder->$operator($this->getField(), $values);
    }
}
