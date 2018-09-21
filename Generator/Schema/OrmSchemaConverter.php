<?php

namespace Naldz\Bundle\FixturamaBundle\Fixturama\Schema;

use Naldz\Bundle\FixturamaBundle\Fixturama\ModelFixtureGenerator;
use Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelException;
use Naldz\Bundle\FixturamaBundle\Fixturama\Exception\UnknownModelFieldException;

class OrmSchemaConverter
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