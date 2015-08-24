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
        $result = $queryBuilder->toSql();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param $requestProvider array
     * @param $expectedResult string
     * @dataProvider providerWithErrorsTestParser
     * @expectedException \QueryParser\QueryParserException
     */
    public function testParserWithErrors($requestProvider, $expectedResult)
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
        $result = $queryBuilder->toSql();
    }

    public function providerTestParser()
    {
        return [
            [['sort' => '-id', 'id' => '2'], 'select * from `test` where (`id` = ?) order by `id` desc'],
            [['sort' => 'id', 'id' => '2,10'], 'select * from `test` where (`id` = ? or `id` = ?) order by `id` asc'],
            [['to' => 'r.lacerda83@gmail.com'], 'select * from `test` where (`to` = ?)'],
            [['to' => 'r.lacerda83@gmail.com', 'id' => '5'], 'select * from `test` where (`to` = ?) and (`id` = ?)'],
        ];
    }

    public function providerWithErrorsTestParser()
    {
        return [
            [['sort' => '-id', 'idx' => '2'], 'select * from `tester` where (`ids` = ?) order by `ids` desc'],
            [['sort' => 'idx', 'id' => '2,10'], 'select * from `teston` where (`id` = ? or `id` = ?) order by `id` asc'],
            [['tor' => 'r.lacerda83@gmail.com'], 'select * from `test` where (`tor` = ?)'],
            [['to' => 'r.lacerda83@gmail.com', 'idx' => '5'], 'select * from `testao` where (`to` = ?) and (`idm` = ?)'],
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
}
