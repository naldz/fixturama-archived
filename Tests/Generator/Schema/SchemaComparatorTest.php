<?php

namespace Naldz\Bundle\FixturamaBundle\Tests\Unit\Fixturama\Schema;

use Naldz\Bundle\FixturamaBundle\Fixturama\Schema\SchemaComparator;


class SchemaComparatorTest extends \PHPUnit_Framework_TestCase
{
    private $sut;

    public function setUp()
    {
        $schemaDefinitionMock = $this->createSchemaDefinitionMock(array(
            'db1' => array(
                'table1' => array(
                    'field1' => array('type'=> 'numberBetween', 'params' => array(0, 99999)),
                    'field2' => array('type' => 'name')
                ),
                'table2' => array(
                    'field1' => array('type'=> 'numberBetween', 'params' => array(0, 99999)),
                    'field2' => array('type'=> 'numberBetween', 'params' => array(0, 99999))
                )
            ),
            'db2' => array(
                'table1' => array(
                    'field1' => array('type'=> 'numberBetween', 'params' => array(0, 99999)),
                )
            )
        ));
        $this->sut = new SchemaComparator($schemaDefinitionMock);
    }

    public function testNewEntitiesAreDetected()
    {
        $schemaDiff = $this->sut->compare(array(
            'db1' => array(
                'table1' => array(
                    'field1' => array('type' => 'INT'),
                    'field2' => array('type' => 'TEXT'),
                ),
                'table2' => array(
                    'field1' => array('type' => 'INT'),
                    'field2' => array('type' => 'TEXT')
                )
            ),
            'db2' => array(
                'table1' => array(
                    'field1' => array('type' => 'INT'),
                    'field2' => array('type' => 'TEXT'),
                ),
                'table2' => array(
                    'field1' => array('type' => 'INT'),
                    'field2' => array('type' => 'TEXT'),
                )
            ),
            'db3' => array(
                'table1' => array(
                    'field1' => array('type' => 'INT'),
                    'field2' => array('type' => 'TEXT'),
                ),
                'table2' => array(
                    'field1' => array('type' => 'INT'),
                    'field2' => array('type' => 'TEXT'),
                )
            )
        ));

        $this->assertEquals($schemaDiff['added_databases'], array('db3'));
        $this->assertEquals($schemaDiff['added_tables'], array('db2.table2'));
        $this->assertEquals($schemaDiff['added_fields'], array('db2.table1.field2'));
    }

    public function testRemovedEntitiesAreDetected()
    {
        $schemaDiff = $this->sut->compare(array(
            'db1' => array(
                'table1' => array(
                    'field1' => array('type' => 'INT')
                ),
            ),
        ));

        $this->assertEquals($schemaDiff['removed_databases'], array('db2'));
        $this->assertEquals($schemaDiff['removed_tables'], array('db1.table2'));
        $this->assertEquals($schemaDiff['removed_fields'], array('db1.table1.field2'));
    }


    private function createSchemaDefinitionMock($data = array())
    {
        $mock = $this->getMockBuilder('Naldz\Bundle\FixturamaBundle\Fixturama\Schema\SchemaDefinition')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getDatabaseNames')
            ->will($this->returnValue(array_keys($data)));

        $mock->expects($this->any())
            ->method('getModelNames')
            ->will($this->returnCallback(
                function($databaseName) use ($data) {
                    if (array_key_exists($databaseName, $data)) {
                        if ($data[$databaseName] instanceof \Exception) {
                            throw $data[$databaseName];
                        }
                        return array_keys($data[$databaseName]);
                    }
                }
            ));

        $mock->expects($this->any())
            ->method('getModelFieldNames')
            ->will($this->returnCallback(
                function($databaseName, $modelName) use ($data) {
                    if (array_key_exists($modelName, $data[$databaseName])) {
                        if ($data[$databaseName][$modelName] instanceof \Exception) {
                            throw $data[$databaseName][$modelName];
                        }
                        return array_keys($data[$databaseName][$modelName]);
                    }
                }
            ));

        return $mock;
    }

}
