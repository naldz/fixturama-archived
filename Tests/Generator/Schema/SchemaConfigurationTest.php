<?php

namespace Naldz\Bundle\FixturamaBundle\Tests\Unit\Fixturama;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;
use Naldz\Bundle\FixturamaBundle\Fixturama\Schema\SchemaConfiguration;


class SchemaConfigurationTest extends \PHPUnit_Framework_TestCase
{
    private $kernel;
    private $configuration;
    
    protected function setUp()
    {
        $this->configuration = new SchemaConfiguration();
    }

    public function testInvalidSchemaOptionShouldThrowException()
    {
        $schema = Yaml::parse("
schema:
    databases:
        db1:
            dsn: test_dsn
            models:
                blog: 
                    fields:
                        id: { type: numberBetween, params: [0, 99999] }
                        title: { type: sentence, params: [10, true] }
                        content: { type: sentence }
");
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($this->configuration, $schema);
    }

    /**
     * @dataProvider missingOptionDataProvider
     */
    public function testMissingRequiredOptionShouldThrowException($schemaString)
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $schema = Yaml::parse($schemaString);
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($this->configuration, $schema);
    }


    public function missingOptionDataProvider()
    {
        return array(
            array("
schema:
    models_error:
        blog: 
            fields:
                id: { type: numberBetween, params: [0, 99999] }
                title: { type: sentence, params: [10, true] }
                content: { type: sentence }
            ")
        );
    }

}
