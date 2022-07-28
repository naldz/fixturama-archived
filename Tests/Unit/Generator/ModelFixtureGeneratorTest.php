<?php

namespace Naldz\Fixturama\Tests\Unit\Generator;

use Naldz\Fixturama\Generator\ModelFixtureGenerator;

class FixtureModeltest extends \PHPUnit_Framework_TestCase
{
    private $modelName = 'blog_post';
    private $definition = array(
        'fields' => array(
            'id' => array(
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
    );
    private $fakerMockDefinition = null;

    public function setUp()
    {
        $this->fakerMockDefinition = array(
            'numberBetween' => array(
                'params' => array(0, 99999),
                'result' => 101
            ),
            'sentence' => array(
                'params' => array(10, true),
                'result' => 'This is a fake sentence.'
            ),
            'word' => array(
                'result' => 'fake_tag'
            )
        );
    }

    public function testGeneratingWithUnknownDataPresetFieldsThrowsException()
    {
        $this->setExpectedException('Naldz\Fixturama\Exception\UnknownModelFieldException');
        $fakerMock = $this->createFakerMock($this->fakerMockDefinition);
        $fixtureModel = new ModelFixtureGenerator($fakerMock);
        $fixtureModel->generate($this->definition, array(
            'unknown_field' => 'test data'
        ));
    }

    public function testFakerMethodAccessFailureThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');
        //make the title field throw an exception
        $this->fakerMockDefinition['sentence']['result'] = new \InvalidArgumentException('sentence field with error test exception');
        $fakerMock = $this->createFakerMock($this->fakerMockDefinition);

        $fixtureModel = new ModelFixtureGenerator($fakerMock);
        $fixtureModel->generate($this->definition);
    }

    public function testFakerAttributeAccessFailureThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');
        //make the title field throw an exception
        $this->fakerMockDefinition['word']['result'] = new \InvalidArgumentException('word field with error test exception');
        $fakerMock = $this->createFakerMock($this->fakerMockDefinition);

        $fixtureModel = new ModelFixtureGenerator($fakerMock);
        $fixtureModel->generate($this->definition);
    }

    public function testGenerationWithoutPresetDataShouldDefaultData()
    {
        $fakerMock = $this->createFakerMock($this->fakerMockDefinition);
        $fixtureModel = new ModelFixtureGenerator($fakerMock);

        $dataFixture = $fixtureModel->generate($this->definition);
        $expectedDataFixture = array(
            'id' => 101,
            'title' => 'This is a fake sentence.',
            'tag' => 'fake_tag'
        );
        $this->assertEquals($dataFixture, $expectedDataFixture);
    }

    public function testGenerationWithPresetDataShouldOverrideDafaultData()
    {
        $fakerMock = $this->createFakerMock($this->fakerMockDefinition);
        $fixtureModel = new ModelFixtureGenerator($fakerMock);

        $dataFixture = $fixtureModel->generate($this->definition, array(
            'title' => 'overridden title sentence'
        ));
        $expectedDataFixture = array(
            'id' => 101,
            'title' => 'overridden title sentence',
            'tag' => 'fake_tag'
        );
        $this->assertEquals($dataFixture, $expectedDataFixture);
    }

    public function createFakerMock($data)
    {
        $fakerMock = $this->getMock('Faker\Generator');

        $fakerMock->expects($this->any())
            ->method('__get')
            ->will($this->returnCallback(
                function($fieldName) use ($data) { 
                    if (array_key_exists($fieldName, $data)) {
                        if ($data[$fieldName]['result'] instanceof \Exception) {
                            throw $data[$fieldName]['result'];
                        }
                        return $data[$fieldName]['result'];
                    }
                    else {
                        return null;
                    }
                }
            ));

        $fakerMock->expects($this->any())
            ->method('__call')
            ->will($this->returnCallback(
                function($fieldName, $params) use ($data) { 
                    if (array_key_exists($fieldName, $data)) {
                        if ($data[$fieldName]['result'] instanceof \Exception) {
                            throw $data[$fieldName]['result'];
                        }
                        return $data[$fieldName]['result'];
                    }
                    else {
                        return null;
                    }
                }
            ));

        return $fakerMock;
    }

}
