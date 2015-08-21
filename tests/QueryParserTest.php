<?php

namespace QueryParser\Tests;

use Illuminate\Http\Request;
use PHPUnit_Framework_TestCase;

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
    function testParser($requestProvider, $expectedResult)
    {
        $request = new Request();
        foreach ($requestProvider as $key => $value) {
            $request->merge(array($key => $value));
        }

        $arrayFields = $this->getFields();

        $QueryParser = $this->getMockBuilder('QueryParser\ParserRequest')
            ->setConstructorArgs(array($request, $this->model))
            ->setMethods(array('setColumnsNames'))
            ->getMock();

        $reflection = new \ReflectionClass($QueryParser);

        $reflectionProperty = $reflection->getProperty('columnNames');
        $reflectionProperty->setAccessible( true );
        $reflectionProperty->setValue($QueryParser, $arrayFields);

        $queryBuilder = $QueryParser->parser();
        $result = $queryBuilder->toSql();

        $this->assertEquals($expectedResult, $result);
    }

    public function providerTestParser()
    {
        return array(
            array(array('sort' => '-id', 'id' => '2'), "select * from `test` where (`id` = ?) order by `id` desc"),
            array(array('sort' => 'id', 'id' => '2,10'), "select * from `test` where (`id` = ? or `id` = ?) order by `id` asc"),
            array(array('to' => 'r.lacerda83@gmail.com'), "select * from `test` where (`to` = ?)"),
            array(array('to' => 'r.lacerda83@gmail.com', 'id' => '5'), "select * from `test` where (`to` = ?) and (`id` = ?)"),
        );
    }

    private function getFields() {
        return array(
            'id' => 'id',
            'to' => 'to',
            'from' => 'from'
        );
    }

}