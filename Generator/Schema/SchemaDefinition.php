<?php

namespace Naldz\Bundle\FixturamaBundle\Fixturama\Schema;

use Naldz\Bundle\FixturamaBundle\Fixturama\ModelFixtureGenerator;
use Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownDatabaseException;
use Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelException;
use Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelFieldException;

class SchemaDefinition
{
    private $rawDefinition = null;

    public function __construct($rawDefinition)
    {
        $this->rawDefinition = $rawDefinition;
    }

    public function getDatabaseDefinition($databaseName)
    {
        if (!isset($this->rawDefinition['databases'][$databaseName])) {
            throw new UnknownDatabaseException(sprintf('Unknown database: %s', $databaseName));
        }

        return $this->rawDefinition['databases'][$databaseName];
    }

    public function getModelDefinition($databaseName, $modelName)
    {
        $databaseDefinition = $this->getDatabaseDefinition($databaseName);

        if (!isset($databaseDefinition['models'][$modelName])) {
            throw new UnknownModelException(sprintf('Unknown model: %s', $modelName));
        }

        return $databaseDefinition['models'][$modelName];
    }

    public function getModelFieldDefinition($databaseName, $modelName, $fieldName)
    {
        $modelDefinition = $this->getModelDefinition($databaseName, $modelName);
        if (!isset($modelDefinition['fields'][$fieldName])) {
            throw new UnknownModelFieldException(sprintf('Unknown model field: %s', $fieldName));
        }

        return $modelDefinition['fields'][$fieldName];
    }

    public function getDatabaseNames()
    {
        return array_keys($this->rawDefinition['databases']);
    }

    public function getModelNames($databaseName = null, $includeDatabaseName = false)
    {
        if (is_null($databaseName) && !$includeDatabaseName) {
            throw new \InvalidArgumentException('Parameter "includeDatabaseName" is required to be true when parameter "databaseName" is null');
        }

        if (!is_null($databaseName)) {
            $databaseDefinition = $this->getDatabaseDefinition($databaseName);
            if ($includeDatabaseName) {
                $data = array($databaseName => array_keys($databaseDefinition['models']));
                return $this->concatDabataseAndModelNames($data);
            }
            return array_keys($databaseDefinition['models']);
        }

        $databaseNames = $this->getDatabaseNames();
        $modelNames = array();
        foreach ($databaseNames as $dbName) {
            $modelNames = array_merge($modelNames, $this->getModelNames($dbName, true));
        }
        return $modelNames;
    }

    private function concatDabataseAndModelNames($data)
    {
        $modelNames = array();
        foreach ($data as $dbName => $models) {
            foreach ($models as $modelName) {
                $modelNames[] = $dbName.'.'.$modelName;
            }
        }
        return $modelNames;
    }

    public function getModelFieldNames($databaseName, $modelName)
    {
        $modelDefinition = $this->getModelDefinition($databaseName, $modelName);
        return array_keys($modelDefinition['fields']);
    }

}