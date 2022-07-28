<?php

namespace Naldz\Fixturama\Tests\Unit\Loader;

use Naldz\Fixturama\Loader\FixtureLoader;

class FixtureLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSuccessfullFixtureLoading()
    {
        $schemaDefinitionMock = $this->createSchemaDefinitionMock(array('db.table'));
        $sqlConverterMock = $this->createSqlConverterMock(array(
            'db.table' => "INSERT INTO db.table (field1, field2) VALUES ('value1_1','value1_2'),('value2_1','value2_2');"
        ));
        $pdoMock = $this->createPdoMock();
        // $eventDispatcherMock = $this->createEventDispatcherMock(array(
        //     array(FixturamaEvent::DATA_ROW_LOAD_PRE, 'db.table', array('field1' => 'value1_1', 'field2' => 'value1_2')),
        //     array(FixturamaEvent::DATA_ROW_LOAD_POST, 'db.table', array('field1' => 'value1_1', 'field2' => 'value1_2')),
        //     array(FixturamaEvent::DATA_ROW_LOAD_PRE, 'db.table', array('field1' => 'value2_1', 'field2' => 'value2_2')),
        //     array(FixturamaEvent::DATA_ROW_LOAD_POST, 'db.table', array('field1' => 'value2_1', 'field2' => 'value2_2'))
        // ));
        $sut = new FixtureLoader($schemaDefinitionMock, $sqlConverterMock, $pdoMock);
        $sut->load(array(
            'db.table' => array(
                array('field1' => 'value1_1', 'field2' => 'value1_2'),
                array('field1' => 'value2_1', 'field2' => 'value2_2')
            )
        ));
    }

    public function testUnknownModelsInFixtureDataThrowsException()
    {
        $this->setExpectedException('Naldz\Fixturama\Exception\UnknownModelException');
        $schemaDefinitionMock = $this->createSchemaDefinitionMock(array('db.table'));
        $sqlConverterMock = $this->createSqlConverterMock();
        $pdoMock = $this->createPdoMock();
        // $eventDispatcherMock = $this->createEventDispatcherMock(array());
        $sut = new FixtureLoader($schemaDefinitionMock, $sqlConverterMock, $pdoMock);
        $sut->load(array(
            'db.unknown_table' => array(
                array('field1' => 'value1_1', 'field2' => 'value1_2'),
                array('field1' => 'value2_1', 'field2' => 'value2_2')
            )
        ));
    }

    private function createSchemaDefinitionMock($modelNames = array())
    {
        $mock = $this->getMockBuilder('Naldz\Fixturama\Schema\SchemaDefinition')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getModelNames')
            ->will($this->returnValue($modelNames));

        return $mock;
    }

    private function createSqlConverterMock($data = array())
    {
        $mock = $this->getMockBuilder('Naldz\Fixturama\Loader\SqlConverter')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('convert')
            ->will($this->returnCallback(
                function($modelName, $dataset) use ($data) {
                    if (array_key_exists($modelName, $data)) {
                        if ($data[$modelName] instanceof \Exception) {
                            throw $data[$modelName];
                        }
                        return $data[$modelName];
                    }
                }
            ));

        return $mock;
    }

    private function createPdoMock($expectedSql = null)
    {
        $mock = $this->getMockBuilder('Naldz\PdoMock\Mock\MockablePdo')
            ->getMock();

        return $mock;
    }

    // private function createEventDispatcherMock($events = array())
    // {
    //     $mock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
    //         ->disableOriginalConstructor()
    //         ->getMock();

    //     $invokeCount = 0;
    //     $mock->expects($this->exactly(count($events)))
    //         ->method('dispatch')
    //         ->will($this->returnCallback(function($eventName, RowDataLoadEvent $event) use ($events, &$invokeCount) {

    //             // if (!isset($events[$eventName])) {
    //             //     $this->fail(sprintf('Unexpected event "%s"', $eventName));
    //             // }

    //             $this->assertEquals($events[$invokeCount][0], $eventName);
    //             $this->assertEquals($events[$invokeCount][1], $event->getModelName());
    //             $this->assertEquals($events[$invokeCount][2], $event->getData());

    //             $invokeCount++;
    //         }));

    //     return $mock;
    // }
}