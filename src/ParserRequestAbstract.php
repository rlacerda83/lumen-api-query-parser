<?php

namespace QueryParser;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

abstract class ParserRequestAbstract
{
    /**
     * @ self::sort
     */
    const SORT_IDENTIFIER = 'sort';
    const SORT_DIRECTION_ASC = 'asc';
    const SORT_DIRECTION_DESC = 'desc';
    const SORT_DELIMITER = ',';
    const SORT_DESC_IDENTIFIER = '-';

    /**
     * @ self::filter
     */
    const FILTER_IDENTIFIER = 'filter';
    const FILTER_DELIMITER = ',';

    /**
     * @ self::column
     */
    const COLUMN_IDENTIFIER = 'columns';
    const COLUMN_DELIMITER = ',';

    /**
     * @var Request
     */
    protected $request;

    protected $model;

    /**
     * @var array
     */
    protected $tables;

    /**
     * @var array
     */
    protected $columnNames;

    protected $queryBuilder;

    protected $fieldErrors = [];

    /**
     * @param Request $request
     * @param $model
     * @param null $queryBuilder
     */
    public function __construct(Request $request, $model, $queryBuilder = null)
    {
        $this->request = $request;
        $this->model = $model;

        $this->tables[] = $this->model->getTable();

        $this->queryBuilder = $queryBuilder ? $queryBuilder : DB::table($this->tables[0]);
        $this->setColumnsNames();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function parser()
    {
        $data = $this->request->except('page');
        foreach ($data as $field => $value) {
            $this->cleanField($field);
            $value = $this->cleanValue($value);

            if ($field == self::SORT_IDENTIFIER) {
                $this->addSort($value);
                continue;
            }

            if ($field == self::COLUMN_IDENTIFIER) {
                $this->addColumn($value);
                continue;
            }

            $this->addFilter($field, $value);
        }

        if (! empty($this->fieldErrors)) {
            throw new QueryParserException($this->fieldErrors);
        }

        return $this->queryBuilder;
    }

    /**
     * @param $field
     * @param $value
     * @throws \Exception
     */
    private function addFilter($field, $value)
    {
        $filter = $this->parseFilter($field);
        $this->findErrors($filter->getField(), self::FILTER_IDENTIFIER);

        $values = explode(self::FILTER_DELIMITER, $value);
        $filter->applyFilter($this->queryBuilder, $values);
    }

    /**
     * @param $value
     * @throws \Exception
     */
    private function addSort($value)
    {
        $fields = explode(self::SORT_DELIMITER, $value);

        foreach ($fields as $field) {
            $filter = $this->parseFilter($field);
            $extractField = $filter->getField();

            $this->cleanField($extractField);
            $direction = self::SORT_DIRECTION_ASC;

            if (strpos($extractField, self::SORT_DESC_IDENTIFIER) > 0) {
                $direction = self::SORT_DIRECTION_DESC;
                $extractField = str_replace(self::SORT_DESC_IDENTIFIER, '', $extractField);
            }

            $this->findErrors($extractField, self::SORT_IDENTIFIER);

            $this->queryBuilder->orderBy($extractField, $direction);
        }
    }

    /**
     * @param $value
     * @throws \Exception
     */
    private function addColumn($value)
    {
        $fields = explode(self::COLUMN_DELIMITER, $value);
        foreach ($fields as &$field) {
            $this->cleanField($field);
            $field = $this->parseFilter($field)->getField();

            $this->findErrors($field, self::COLUMN_IDENTIFIER);
        }
        $this->queryBuilder->select($fields);
    }

    protected function findErrors($field, $type)
    {
        $explodedField = explode('.', $field);
        $table = $explodedField[0];
        $shortField = $explodedField[1];

        if (!isset($this->columnNames[$table])) {
            $this->fieldErrors[$type][] = 'Field ['.$field.'] not allowed for search';
            return;
        }

        if (array_search($shortField, $this->columnNames[$table]) === false) {
            $this->fieldErrors[$type][] = 'Field ['.$field.'] not allowed for search';
        }
    }

    protected function cleanValue($string)
    {
        return trim($string);
    }

    protected function cleanField(&$string)
    {
        $string = strtolower(trim($string));
    }

    public function addTables(array $tables)
    {
        $this->tables = array_merge($this->tables, $tables);
        $this->setColumnsNames();
    }

    private function parseFilter($field)
    {
        return new Filter($field, $this->tables);
    }

    abstract protected function setColumnsNames();
}