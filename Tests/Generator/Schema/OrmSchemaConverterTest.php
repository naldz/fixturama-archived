<?php

namespace Naldz\Bundle\FixturamaBundle\Tests\Unit\Fixturama;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;
use Naldz\Bundle\FixturamaBundle\Fixturama\Schema\OrmSchemaConverter;


class OrmSchemaConverterTest extends \PHPUnit_Framework_TestCase
{
    private $sut;

    public function setUp()
    {
        $this->sut = new OrmSchemaConverter();
    }

    public function testSuccessfulConversion()
    {
        $expectedRawSchemaDefinition = array(
            'db1' => array(
                'table1' => array(
                    'table1_field1' => array('type' => 'INT'),
                    'table1_field2' => array('type' => 'TEXT'),
                ),
                'table2' => array(
                    'table2_field1' => array('type' => 'INT'),
                    'table2_field2' => array('type' => 'TEXT'),
                )
            )
        );

        $domMock = $this->createDomDocumentMock(array(), array(
            $this->createDomElementMock(array('name' => 'db1'), array(
                $this->createDomElementMock(array('name' => 'table1'), array(
                    $this->createDomElementMock(array('name' => 'table1_field1', 'type' => 'INT')),
                    $this->createDomElementMock(array('name' => 'table1_field2', 'type' => 'TEXT'))
                )),
                $this->createDomElementMock(array('name' => 'table2'), array(
                    $this->createDomElementMock(array('name' => 'table2_field1', 'type' => 'INT')),
                    $this->createDomElementMock(array('name' => 'table2_field2', 'type' => 'TEXT'))
                ))
            ))
        ));

        $actualRawSchemaDefinition = $this->sut->convert($domMock);
        $this->assertEquals($actualRawSchemaDefinition, $expectedRawSchemaDefinition);
    }

    private function createDomDocumentMock($attributes = array(), $children = array())
    {
        $domMock = $this->getMockBuilder('\DOMDocument')
            ->disableOriginalConstructor()
            ->getMock();

        $domMock->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnCallback(
                function($attrName) use ($attributes) {
                    if (array_key_exists($attrName, $attributes)) {
                        return $attributes[$attrName];
                    }
                    return null;
                }
            ));

        $domMock->expects($this->any())
            ->method('getElementsByTagName')
            ->will($this->returnValue($children));

        return $domMock;
    }

    private function createDomElementMock($attributes = array(), $children = array())
    {
        $domElementMock = $this->getMockBuilder('\DOMElement')
            ->disableOriginalConstructor()
            ->getMock();

        $domElementMock->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnCallback(
                function($attrName) use ($attributes) {
                    if (array_key_exists($attrName, $attributes)) {
                        return $attributes[$attrName];
                    }
                    return null;
                }
            ));

        $domElementMock->expects($this->any())
            ->method('getElementsByTagName')
            ->will($this->returnValue($children));

        return $domElementMock;
    }

}
