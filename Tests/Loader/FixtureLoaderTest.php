<?php

namespace Naldz\Bundle\FixturamaBundle\Tests\Unit\Fixturama;

use Naldz\Bundle\FixturamaBundle\Fixturama\FixtureLoader;
use Naldz\Bundle\FixturamaBundle\Fixturama\Event\FixturamaEvent;
use Naldz\Bundle\FixturamaBundle\Fixturama\Event\RowDataLoadEvent;

class FixtureLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSuccessfullFixtureLoading()
    {
//         $expectedSql = "SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
// SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
// SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';
// INSERT INTO db.table (field1, field2) VALUES ('value1_1','value1_2'),('value2_1','value2_2');
// SET SQL_MODE=@OLD_SQL_MODE;
// SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
// SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;";

        $schemaDefinitionMock = $this->createSchemaDefinitionMock(array('db.table'));
        $sqlConverterMock = $this->createSqlConverterMock(array(
            'db.table' => "INSERT INTO db.table (field1, field2) VALUES ('value1_1','value1_2'),('value2_1','value2_2');"
        ));
        $pdoMock = $this->createPdoMock();
        $eventDispatcherMock = $this->createEventDispatcherMock(array(
            array(FixturamaEvent::DATA_ROW_LOAD_PRE, 'db.table', array('field1' => 'value1_1', 'field2' => 'value1_2')),
            array(FixturamaEvent::DATA_ROW_LOAD_POST, 'db.table', array('field1' => 'value1_1', 'field2' => 'value1_2')),
            array(FixturamaEvent::DATA_ROW_LOAD_PRE, 'db.table', array('field1' => 'value2_1', 'field2' => 'value2_2')),
            array(FixturamaEvent::DATA_ROW_LOAD_POST, 'db.table', array('field1' => 'value2_1', 'field2' => 'value2_2'))
        ));
        $sut = new FixtureLoader($schemaDefinitionMock, $sqlConverterMock, $pdoMock, $eventDispatcherMock);
        $sut->load(array(
            'db.table' => array(
                array('field1' => 'value1_1', 'field2' => 'value1_2'),
                array('field1' => 'value2_1', 'field2' => 'value2_2')
            )
        ));
    }

    public function testUnknownModelsInFixtureDataThrowsException()
    {
        $this->setExpectedException('Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelException');
        $schemaDefinitionMock = $this->createSchemaDefinitionMock(array('db.table'));
        $sqlConverterMock = $this->createSqlConverterMock();
        $pdoMock = $this->createPdoMock();
        $eventDispatcherMock = $this->createEventDispatcherMock(array());
        $sut = new FixtureLoader($schemaDefinitionMock, $sqlConverterMock, $pdoMock, $eventDispatcherMock);
        $sut->load(array(
            'db.unknown_table' => array(
                array('field1' => 'value1_1', 'field2' => 'value1_2'),
                array('field1' => 'value2_1', 'field2' => 'value2_2')
            )
        ));
    }

    private function createSchemaDefinitionMock($modelNames = array())
    {
        $mock = $this->getMockBuilder('Naldz\Bundle\FixturamaBundle\Fixturama\Schema\SchemaDefinition')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getModelNames')
            ->will($this->returnValue($modelNames));

        return $mock;
    }

    private function createSqlConverterMock($data = array())
    {
        $mock = $this->getMockBuilder('Naldz\Bundle\FixturamaBundle\Fixturama\SqlConverter')
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
        $mock = $this->getMockBuilder('Naldz\Bundle\TestUtilityBundle\Pdo\Mock\MockablePdo')
            ->getMock();

        // $mock->expects($this->any())
        //     ->method('exec')
        //     ->with($expectedSql);

        return $mock;
    }

    private function createEventDispatcherMock($events = array())
    {
        $mock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $invokeCount = 0;
        $mock->expects($this->exactly(count($events)))
            ->method('dispatch')
            ->will($this->returnCallback(function($eventName, RowDataLoadEvent $event) use ($events, &$invokeCount) {

                // if (!isset($events[$eventName])) {
                //     $this->fail(sprintf('Unexpected event "%s"', $eventName));
                // }

                $this->assertEquals($events[$invokeCount][0], $eventName);
                $this->assertEquals($events[$invokeCount][1], $event->getModelName());
                $this->assertEquals($events[$invokeCount][2], $event->getData());

                $invokeCount++;
            }));

        return $mock;
    }
}