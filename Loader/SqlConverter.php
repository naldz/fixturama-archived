<?php

namespace Naldz\Bundle\FixturamaBundle\Fixturama;

use Naldz\Bundle\FixturamaBundle\Fixturama\ModelFixtureGenerator;
use Naldz\Bundle\FixturamaBundle\Fixturama\Schema\SchemaDefinition;
use Naldz\Bundle\FixturamaBundle\Fixturama\Exception\IncompleteDatasetException;

class SqlConverter
{
    private $schemaDefinition = null;
    private $pdo = null;

    public function __construct(SchemaDefinition $schemaDefinition, \PDO $pdo)
    {
        $this->schemaDefinition = $schemaDefinition;
        $this->pdo = $pdo;
    }

    public function convert($tableName, $dataset)
    {
        $keys = explode('.', $tableName);
        if (count($keys) != 2) {
            throw new InvalidDatabaseAndModelNameCombinationException(sprintf('The database and model name combination "%s" is not valid. It should be in the format "database.model"', $tableName));
        }
        $databaseName = $keys[0];
        $modelName = $keys[1];

        $rawModelDefinition = $this->schemaDefinition->getModelDefinition($databaseName, $modelName);
        $fieldNames = array_keys($rawModelDefinition['fields']);

        $sqlInsertValues = array();
        foreach ($dataset as $rowIndex => $rowData) {
            $noValueFields = array_diff($fieldNames, array_keys($rowData));
            if (count($noValueFields)) {
                throw new IncompleteDatasetException(sprintf('No value for field/s "%s" on row %i for table "%s"', implode(',', $noValueFields), $rowIndex, $tableName));
            }
            $quotedRowData = array();
            foreach ($rowData as $fieldName => $fieldData) {
                $quotedRowData[$fieldName] = $this->pdo->quote($fieldData);
            }
            $sqlInsertValues[] = '('.implode(',', $quotedRowData).')';
        }

        $fieldSql = sprintf('`%s`', implode('`,`', $fieldNames));
        $sql = sprintf('INSERT INTO %s (%s) VALUES %s', $tableName, $fieldSql, implode(',', $sqlInsertValues).';');

        return $sql;
    }

    public function convertRow($tableName, $rowData)
    {
        $keys = explode('.', $tableName);
        if (count($keys) != 2) {
            throw new InvalidDatabaseAndModelNameCombinationException(sprintf('The database and model name combination "%s" is not valid. It should be in the format "database.model"', $tableName));
        }
        $databaseName = $keys[0];
        $modelName = $keys[1];

        $rawModelDefinition = $this->schemaDefinition->getModelDefinition($databaseName, $modelName);
        $fieldNames = array_keys($rawModelDefinition['fields']);

        $noValueFields = array_diff($fieldNames, array_keys($rowData));
        if (count($noValueFields)) {
            throw new IncompleteDatasetException(sprintf('No value for field/s "%s" for table "%s"', implode(',', $noValueFields), $tableName));
        }

        $quotedRowData = array();
        foreach ($rowData as $fieldName => $fieldData) {
            $quotedRowData[$fieldName] = $this->pdo->quote($fieldData);
        }
        $sqlInsertValues[] = '('.implode(',', $quotedRowData).')';

        $fieldSql = sprintf('`%s`', implode('`,`', $fieldNames));
        $sql = sprintf('INSERT INTO %s (%s) VALUES %s', $tableName, $fieldSql, implode(',', $sqlInsertValues).';');

        return $sql;
    }
}