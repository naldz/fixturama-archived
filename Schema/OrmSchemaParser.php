<?php

namespace Naldz\Fixturama\Schema;

use Naldz\Fixturama\Generator\ModelFixtureGenerator;
use Naldz\Fixturama\Exception\UnknownModelException;
use Naldz\Fixturama\Exception\UnknownModelFieldException;

class OrmSchemaParser
{
    public function convert(\DOMDocument $dom)
    {
        $rawSchema = array();
        $databases = $dom->getElementsByTagName('database');

        foreach ($databases as $database) {
            $dbName = $database->getAttribute('name');
            $rawSchema[$dbName] = array();
            $tables = $database->getElementsByTagName('table');
            foreach ($tables as $table) {
                $tableDefinition = array();
                $cols = $table->getElementsByTagName('column');
                foreach ($cols as $col) {
                    $tableDefinition[$col->getAttribute('name')] = array(
                        'type' => $col->getAttribute('type')
                    );
                }
                $rawSchema[$dbName][$table->getAttribute('name')] = $tableDefinition;
            }
        }

        return $rawSchema;
    }

}