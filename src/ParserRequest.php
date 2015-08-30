<?php

namespace QueryParser;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParserRequest
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
    protected $columnNames;

    protected $queryBuilder;

    protected $fieldErrors = [];

    /**
     * @param Request $request
     * @param $model
     */
    public function __construct(Request $request, $model)
    {
        $this->request = $request;
        $this->model = $model;
        $this->queryBuilder = DB::table($model->getTable());

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
            $field = trim($field);
            if ($field == self::SORT_IDENTIFIER) {
                $this->addSort($value);
            } elseif ($field == self::COLUMN_IDENTIFIER) {
                $this->addColumn($value);
            } else {
                $this->addFilter($field, $value);
            }
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
        $this->findErrors($field, self::FILTER_IDENTIFIER);

        $values = explode(self::FILTER_DELIMITER, $value);

        $this->queryBuilder->where(function ($query) use ($values, $field) {
            foreach ($values as $whereValue) {
                $query->orWhere($field, $whereValue);
            }
        });
    }

    /**
     * @param $value
     * @throws \Exception
     */
    private function addSort($value)
    {
        $fields = explode(self::SORT_DELIMITER, $value);

        foreach ($fields as $field) {
            $direction = self::SORT_DIRECTION_ASC;

            if (substr($field, 0, 1) == self::SORT_DESC_IDENTIFIER) {
                $direction = self::SORT_DIRECTION_DESC;
                $field = str_replace(self::SORT_DESC_IDENTIFIER, '', $field);
            }

            $this->findErrors($field, self::SORT_IDENTIFIER);

            $this->queryBuilder->orderBy($field, $direction);
        }
    }

    /**
     * @param $value
     * @throws \Exception
     */
    private function addColumn($value)
    {
        $fields = explode(self::COLUMN_DELIMITER, $value);
        foreach ($fields as $field) {
            $this->findErrors($field, self::COLUMN_IDENTIFIER);
        }
        $this->queryBuilder->select($fields);
    }

    protected function setColumnsNames()
    {
        $connection = DB::connection();
        $this->columnNames = $connection->getSchemaBuilder()->getColumnListing($this->model->getTable());
    }

    protected function findErrors($field, $type)
    {
        if (array_search($field, $this->columnNames) === false) {
            $this->fieldErrors[$type][] = 'Field ['.$field.'] not found';
        }
    }
}
