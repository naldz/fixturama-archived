<?php

namespace Naldz\Fixturama\Generator;

use Naldz\Fixturama\Exception\UnknownModelFieldException;

class ModelFixtureGenerator
{
    private $faker = null;

    public function __construct($faker)
    {
        $this->faker = $faker;
    }

    public function generate($rawModelDefinition, $dataPresets = array())
    {
        //if a field in the dataPresets is unknown, throw an error
        $unknownFields = array_diff(array_keys($dataPresets), array_keys($rawModelDefinition['fields']));
        if (count($unknownFields)) {
            throw new UnknownModelFieldException(sprintf('Unknown FixtureModelField names: %s', implode(', ', $unknownFields)));
        }

        $rawFieldDefinitions = $rawModelDefinition['fields'];
        $data = array();

        foreach ($rawFieldDefinitions as $fieldName => $rawFieldDefinition) {
            if (isset($dataPresets[$fieldName])) {
                $data[$fieldName] = $dataPresets[$fieldName];
            }
            else {
                $type = $rawFieldDefinition['type'];
                if (array_key_exists('params', $rawFieldDefinition)) {
                    $data[$fieldName] = call_user_func_array(array($this->faker, $type), $rawFieldDefinition['params']);
                }
                else {
                    $data[$fieldName] = $this->faker->$type;
                }
            }
        }

        return $data;
    }
}