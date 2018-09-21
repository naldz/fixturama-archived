<?php

namespace Naldz\Bundle\FixturamaBundle\Tests\Unit\Fixturama\Schema;

use Naldz\Bundle\FixturamaBundle\Fixturama\Schema\SchemaDefinition;

class SchemaDefinitionTest extends \PHPUnit_Framework_TestCase
{
    private $rawDefinition = null;
    private $modelDefinition = null;
    private $idFieldDefinition = array('type' => 'numberBetween', 'params' => array(0, 9999));
    private $titleFieldDefinition = array('type' => 'sentence', 'params' => array(10, true));
    private $schemaDefinition = null;

    public function setUp()
    {
        $this->modelDefinition = array(
            'fields' => array(
                'field1' => $this->idFieldDefinition,
                'field2' => $this->titleFieldDefinition
            )
        );
        $this->rawDefinition = array(
            'databases' => array(
                'db1' => array(
                    'models' => array(
                        'tb1' => $this->modelDefinition
                    )
                ),
                'db2' => array(
                    'models' => array(
                        'tb1' => $this->modelDefinition
                    )
                )
            )
        );
        $this->schemaDefinition = new SchemaDefinition($this->rawDefinition);
    }

    public function testUnknownDatabaseNameThrowsException()
    {
        $this->setExpectedException('Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownDatabaseException');
        $this->schemaDefinition->getDatabaseDefinition('unknown_database');
    }

    public function testUnknownModelNameThrowsException()
    {
        $this->setExpectedException('Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelException');
        $this->schemaDefinition->getModelDefinition('db1','unknown_model');
    }

    public function testUnknownModelFieldNameThrowsException()
    {
        $this->setExpectedException('Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelFieldException');
        $this->schemaDefinition->getModelFieldDefinition('db1', 'tb1', 'unknown_field');
    }

    public function testUnknownDatabaseWhileGettingModelThrowsException()
    {
        $this->setExpectedException('Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownDatabaseException');
        $this->schemaDefinition->getDatabaseDefinition('unknown_database', 'tb1');
    }

    public function testUnknownDatabaseWhileGettingFieldThrowsException()
    {
        $this->setExpectedException('Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownDatabaseException');
        $this->schemaDefinition->getDatabaseDefinition('unknown_database', 'tb1', 'field1');
    }

    public function testUnknownModelWhileGettingFieldThrowsException()
    {
        $this->setExpectedException('Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelException');
        $this->schemaDefinition->getModelFieldDefinition('db1','unknown_model','title');
    }

    public function testSuccessfulGettingOfModelDefinition()
    {
        $actualModelDefinition = $this->schemaDefinition->getModelDefinition('db1', 'tb1');
        $expectedModelDefinition = $this->modelDefinition;
        $this->assertEquals($expectedModelDefinition, $actualModelDefinition);
    }

    public function testSuccessfulGettingOfModelFieldDefinition()
    {
        $actualModelFieldDefinition = $this->schemaDefinition->getModelFieldDefinition('db1', 'tb1', 'field2');
        $expectedModelFieldDefinition = $this->titleFieldDefinition;
        $this->assertEquals($expectedModelFieldDefinition, $actualModelFieldDefinition);
    }

    public function testGettingOfDatabaseNames()
    {
        $actualDatabaseNames = $this->schemaDefinition->getDatabaseNames();
        $expectedDatabaseNames = array('db1','db2');
        $this->assertEquals($expectedDatabaseNames, $actualDatabaseNames);
    }

    public function testGettingOfModelNamesWithoutDatabaseName()
    {
        $actualModelNames = $this->schemaDefinition->getModelNames('db1');
        $expectedModelNames = array('tb1');
        $this->assertEquals($expectedModelNames, $actualModelNames);
    }

    public function testGettingOfModelNamesWithoutDatabaseNameAndIncludeDbNameFalseThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->schemaDefinition->getModelNames(null, false);
    }

    public function testGettingOfAllModelNamesWithConcatTrue()
    {
        $actualModelNames = $this->schemaDefinition->getModelNames(null, true);
        $expectedModelNames = array('db1.tb1','db2.tb1');
        $this->assertEquals($expectedModelNames, $actualModelNames);
    }

    public function testGettingOfModelFieldNames()
    {
        $actualModelFieldNames = $this->schemaDefinition->getModelFieldNames('db1', 'tb1');
        $expectedModelFieldNames = array('field1', 'field2');
        $this->assertEquals($expectedModelFieldNames, $actualModelFieldNames);
    }
}