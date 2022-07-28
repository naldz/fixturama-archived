<?php

namespace Naldz\Fixturama\Loader;

use Naldz\Fixturama\Generator\ModelFixtureGenerator;
use Naldz\Fixturama\Schema\SchemaDefinition;
use Naldz\Fixturama\Loader\SqlConverter;
use Naldz\Fixturama\Exception\UnknownModelException;

class FixtureLoader
{
    private $definition = null;
    private $sqlConverter = null;
    private $pdo = null;

    public function __construct(SchemaDefinition $definition, SqlConverter $sqlConverter, \PDO $pdo)
    {
        $this->definition = $definition;
        $this->sqlConverter = $sqlConverter;
        $this->pdo = $pdo;
    }

    public function load($fixtureData)
    {
        //if a model in the data presets is unknown, throw an exception
        $unknownModels = array_diff(array_keys($fixtureData), $this->definition->getModelNames(null, true));

        if (count($unknownModels)) {
            throw new UnknownModelException(sprintf('Unknown FixtureModel names: %s', implode(', ', $unknownModels)));
        }

        $this->pdo->exec('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;');
        $this->pdo->exec('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;');
        $this->pdo->exec('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'TRADITIONAL,ALLOW_INVALID_DATES\';');

        foreach ($fixtureData as $modelName => $modelFixtureDataset) {
            foreach ($modelFixtureDataset as $rowData) {
                $sql = $this->sqlConverter->convertRow($modelName, $rowData);
                if (false === $this->pdo->exec($sql)) {
                    $error = implode('; ',$this->pdo->errorInfo());
                    throw new \Exception("Failed to load fixture data. ". $error);
                }
            }
        }

        $this->pdo->exec('SET SQL_MODE=@OLD_SQL_MODE;');
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;');
        $this->pdo->exec('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;');
    }
}