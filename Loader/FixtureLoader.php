<?php

namespace Naldz\Bundle\FixturamaBundle\Fixturama;

use Naldz\Bundle\FixturamaBundle\Fixturama\ModelFixtureGenerator;
use Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelException;
use Naldz\Bundle\FixturamaBundle\Fixturama\SqlConverter;
use Naldz\Bundle\FixturamaBundle\Fixturama\Schema\SchemaDefinition;
use Naldz\Bundle\FixturamaBundle\Fixturama\Event\RowDataLoadEvent;
use Naldz\Bundle\FixturamaBundle\Fixturama\Event\FixturamaEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FixtureLoader
{
    private $definition = null;
    private $sqlConverter = null;
    private $pdo = null;
    private $eventDispatcher = null;

    public function __construct(SchemaDefinition $definition, SqlConverter $sqlConverter, \PDO $pdo, EventDispatcherInterface $eventDispatcher)
    {
        $this->definition = $definition;
        $this->sqlConverter = $sqlConverter;
        $this->pdo = $pdo;
        $this->eventDispatcher = $eventDispatcher;
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
                $this->eventDispatcher->dispatch(FixturamaEvent::DATA_ROW_LOAD_PRE, new RowDataLoadEvent($modelName, $rowData));

                if (false === $this->pdo->exec($sql)) {
                    $error = implode('; ',$this->pdo->errorInfo());
                    throw new \Exception("Failed to load fixture data. ". $error);
                }

                $this->eventDispatcher->dispatch(FixturamaEvent::DATA_ROW_LOAD_POST, new RowDataLoadEvent($modelName, $rowData));
            }
        }
        
        $this->pdo->exec('SET SQL_MODE=@OLD_SQL_MODE;');
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;');
        $this->pdo->exec('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;');

    }
}