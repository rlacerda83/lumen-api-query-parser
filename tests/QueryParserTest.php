<?php

namespace QueryParser\Tests;

use Illuminate\Http\Request;
use PHPUnit_Framework_TestCase;
use QueryParser\ParserRequest;

class ParserRequestTest extends PHPUnit_Framework_TestCase
{
    protected $model = null;

    public function setUp()
    {
        \Dotenv::load(__DIR__.'/../');

        $app = new \Laravel\Lumen\Application(
            realpath(__DIR__.'/../')
        );

        $app->withFacades();

        $this->model = new BaseModel();
    }

    public function tearDown()
    {
    }

    /**
     * @param $requestProvider array
     * @param $expectedResult string
     *
     * @dataProvider providerTestParser
     */
    public function testParser($requestProvider, $expectedResult)
    {
        $result = $this->manageRequest($requestProvider);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param $requestProvider array
     * @dataProvider providerWithErrorsTestParser
     * @expectedException \QueryParser\QueryParserException
     */
    public function testParserWithErrors($requestProvider)
    {
        $result = $this->manageRequest($requestProvider);
    }

    public function providerTestParser()
    {
        return [
            [['columns' => 'id,to', 'id' => '5'], 'select `test`.`id`, `test`.`to` from `test` where (`test`.`id` = ?)'],
            [['sort' => '-id', 'id' => '2'], 'select * from `test` where (`test`.`id` = ?) order by `test`.`id` desc'],
            [['sort' => 'id', 'id' => '2,10'], 'select * from `test` where (`test`.`id` = ? or `test`.`id` = ?) order by `test`.`id` asc'],
            [['to' => 'r.lacerda83@gmail.com'], 'select * from `test` where (`test`.`to` = ?)'],
            [['to' => 'r.lacerda83@gmail.com', 'id' => '5'], 'select * from `test` where (`test`.`to` = ?) and (`test`.`id` = ?)'],
        ];
    }

    public function providerWithErrorsTestParser()
    {
        return [
            [['columns' => 'id' , 'sort' => '-id', 'idx' => '2'], 'select * from `tester` where (`test`.`ids` = ?) order by `test`.`ids` desc'],
            [['sort' => 'idx', 'id' => '2,10'], 'select * from `teston` where (`teston`.`id` = ? or `teston`.`id` = ?) order by `teston`.`id` asc'],
            [['tor' => 'r.lacerda83@gmail.com'], 'select * from `test` where (`test`.`tor` = ?)'],
            [['to' => 'r.lacerda83@gmail.com', 'idx' => '5'], 'select * from `testao` where (`testao`.`to` = ?) and (`testao`.`idm` = ?)'],
        ];
    }

    private function getFields()
    {
        return [
            'id' => 'id',
            'to' => 'to',
            'from' => 'from',
        ];
    }

    private function manageRequest($requestProvider)
    {
        $request = new Request();
        foreach ($requestProvider as $key => $value) {
            $request->merge([$key => $value]);
        }

        $arrayFields = $this->getFields();

        $QueryParser = new ParserRequest($request, $this->model);

        $reflection = new \ReflectionClass($QueryParser);
        $reflectionProperty = $reflection->getProperty('columnNames');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($QueryParser, $arrayFields);

        $queryBuilder = $QueryParser->parser();

        return $queryBuilder->toSql();
    }
}