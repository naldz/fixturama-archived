<?php

namespace Naldz\Fixturama\Generator;

use Naldz\Fixturama\Generator\ModelFixtureGenerator;
use Naldz\Fixturama\Schema\SchemaDefinition;
use Naldz\Fixturama\Exception\UnknownModelException;
use Naldz\Fixturama\Exception\UnknownModelFieldException;
use Naldz\Fixturama\Exception\InvalidDatabaseAndModelNameCombinationException;

class FixtureGenerator
{
    private $schemaDefinition = null;
    private $modelFixtureGenerator = null;

    public function __construct(SchemaDefinition $schemaDefinition, $modelFixtureGenerator)
    {
        $this->schemaDefinition = $schemaDefinition;
        $this->modelFixtureGenerator = $modelFixtureGenerator;
    }

    public function generate(Array $dataPresets)
    {
        $fixtureData = array();
        foreach ($dataPresets as $key => $modelPresetDataCollection) {
            //the model name is composed of the database name and the model name separated by "."
            $keys = explode('.', $key);
            if (count($keys) != 2) {
                throw new InvalidDatabaseAndModelNameCombinationException(sprintf('The database and model name combination "%s" is not valid. It should be in the format "database.model"', $key));
            }
            $databaseName = $keys[0];
            $modelName = $keys[1];

            $rawModelDefinition = $this->schemaDefinition->getModelDefinition($databaseName, $modelName);
            $fixtureData[$key] = array();
            foreach ($modelPresetDataCollection as $modelPresetData) {
                try {
                    $fixtureData[$key][] = $this->modelFixtureGenerator->generate($rawModelDefinition, $modelPresetData);
                }
                catch (UnknownModelFieldException $e) {
                    $errorMessage = $e->getMessage().' in model: '.$key;
                    throw new UnknownModelFieldException($errorMessage);
                }
            }
        }

        return $fixtureData;
    }
}