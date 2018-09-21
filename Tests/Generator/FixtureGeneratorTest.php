<?php

namespace Naldz\Bundle\FixturamaBundle\Tests\Unit\Fixturama;

use Naldz\Bundle\FixturamaBundle\Fixturama\FixtureGenerator;
use Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelException;
use Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelFieldException;

class FixtureGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidDatabaseAndModelNameCombinationThrowsException()
    {
        $this->setExpectedException('Naldz\Bundle\FixturamaBundle\Fixturama\Exception\InvalidDatabaseAndModelNameCombinationException');
        $schemaDefinitionMock = $this->createSchemaDefinitionMock();
        $modelFixtureGeneratorMock = $this->createModelFixtureGeneratorMock();

        $sut = new FixtureGenerator($schemaDefinitionMock, $modelFixtureGeneratorMock);
        $sut->generate(array(
            'table1' => array()
        ));
    }

    public function testGettingOfUnknownModelThrowsException()
    {
        $this->setExpectedException('Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelException');
        $schemaDefinitionMock = $this->createSchemaDefinitionMock(array(
            'blog_author' => new UnknownModelException('Fake UnknownModelException')
        ));
        $modelFixtureGeneratorMock = $this->createModelFixtureGeneratorMock();
        $sut = new FixtureGenerator($schemaDefinitionMock, $modelFixtureGeneratorMock);

        $fixtureModel = $sut->generate(array(
            'db1.blog_author' => array(
                array('id' => '1')
            )
        ));
    }

    public function testModelFixtureGeneratorErrorThrowsException()
    {
        $this->setExpectedException('Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelFieldException');
        $schemaDefinitionMock = $this->createSchemaDefinitionMock(array(
            'blog_author' => array(
                'fields' => array(
                    'id' => array(
                        'type'=> 'numberBetween', 
                        'params' => array(0, 99999)
                    ),
                    'title' => array(
                        'type' => 'sentence',
                        'params' => array(10, true)
                    )
                )
            )
        ));
        $modelFixtureGeneratorMock = $this->createModelFixtureGeneratorMock(array(
            new UnknownModelFieldException('Fake UnknownModelFieldException.'),
        ));
        $sut = new FixtureGenerator($schemaDefinitionMock, $modelFixtureGeneratorMock);

        $fixtureModel = $sut->generate(array(
            'db1.blog_author' => array(array('unknown_field1' => 1, 'unknown_field2' => 2))
        ));
    }

    public function testSuccessfulGenerationOfFixtureData()
    {
        $schemaDefinitionMock = $this->createSchemaDefinitionMock(array(
            'blog_author' => array(
                'fields' => array(
                    'id' => array(
                        'type'=> 'numberBetween', 
                        'params' => array(0, 99999)
                    ),
                    'name' => array(
                        'type' => 'name'
                    )
                )
            ),
            'blog_post' => array(
                'fields' => array(
                    'id' => array(
                        'type'=> 'numberBetween', 
                        'params' => array(0, 99999)
                    ),
                    'blog_author_id' => array(
                        'type'=> 'numberBetween', 
                        'params' => array(0, 99999)
                    ),
                    'title' => array(
                        'type' => 'sentence',
                        'params' => array(10, true)
                    ),
                    'tag' => array(
                        'type' => 'word'
                    )
                )
            )
        ));
        $modelFixtureGeneratorMock = $this->createModelFixtureGeneratorMock(array(
            array('id' => 1, 'name' => 'Author One'),
            array('id' => 2, 'name' => 'Author Two'),
            array('id' => 1, 'blog_author_id' => 1, 'title' => 'Post 1', 'tag' => 'tag_one'),
            array('id' => 2, 'blog_author_id' => 2, 'title' => 'Post 2', 'tag' => 'tag_two')
        ));
        $sut = new FixtureGenerator($schemaDefinitionMock, $modelFixtureGeneratorMock);
        $actualFixtureData = $sut->generate(array(
            'db1.blog_author' => array(
                array('id' => 1), 
                array('id' => 2)
            ),
            'db1.blog_post' => array(
                array('id' => 1, 'blog_author_id' => 1),
                array('id' => 2, 'blog_author_id' => 2)
            ),
        ));

        $expectedFixtureData = array(
            'db1.blog_author' => array(
                array('id' => 1, 'name' => 'Author One'),
                array('id' => 2, 'name' => 'Author Two')
            ),
            'db1.blog_post' => array(
                array('id' => 1, 'blog_author_id' => 1, 'title' => 'Post 1', 'tag' => 'tag_one'),
                array('id' => 2, 'blog_author_id' => 2, 'title' => 'Post 2', 'tag' => 'tag_two')
            )
        );
        $this->assertEquals($expectedFixtureData, $actualFixtureData);
    }

    private function createSchemaDefinitionMock($data = array())
    {
        $mock = $this->getMockBuilder('Naldz\Bundle\FixturamaBundle\Fixturama\Schema\SchemaDefinition')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getModelDefinition')
            ->will($this->returnCallback(
                function($databaseName, $modelName) use ($data) {
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

    private function createModelFixtureGeneratorMock($results = array())
    {
        $mock = $this->getMockBuilder('Naldz\Bundle\FixturamaBundle\Fixturama\ModelFixtureGenerator')
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($results as $index => $result) {
            if ($result instanceof \Exception) {
                $mock->expects($this->at($index))
                    ->method('generate')
                    ->will($this->throwException($result));
            }
            else {
                $mock->expects($this->at($index))
                    ->method('generate')
                    ->will($this->returnValue($result));
            }
        }

        return $mock;
    }
}
