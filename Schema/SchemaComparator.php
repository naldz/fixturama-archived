<?php

namespace Naldz\Fixturama\Schema;

use Naldz\Fixturama\Schema\SchemaDefinition;

class SchemaComparator
{
    public function compare(SchemaDefinition $schemaDefinition, $rawOrmSchema)
    {
        $finalAddedDatabaseNames = array();
        $finalRemovedDatabaseNames = array();
        $finalAddedTableNames = array();
        $finalRemovedTableNames = array();
        $finalAddedFieldNames = array();
        $finalRemovedFieldNames = array();

        $ormDatabaseNames = array_keys($rawOrmSchema);
        $fixtureDatabaseNames = $schemaDefinition->getDatabaseNames();

        $finalAddedDatabaseNames = array_values(array_diff($ormDatabaseNames, $fixtureDatabaseNames));
        $finalRemovedDatabaseNames = array_values(array_diff($fixtureDatabaseNames, $ormDatabaseNames));

        foreach ($ormDatabaseNames as $ormDbName) {
            if (in_array($ormDbName, $fixtureDatabaseNames)) {
                $ormTablesNames = array_keys($rawOrmSchema[$ormDbName]);
                $fixtureModelNames = $schemaDefinition->getModelNames($ormDbName);

                $addedTableNames = array_diff($ormTablesNames, $fixtureModelNames);

                foreach ($addedTableNames as $addedTableName) {
                    $finalAddedTableNames[] = $ormDbName.'.'.$addedTableName;
                }

                $removedTableNames = array_diff($fixtureModelNames, $ormTablesNames);
                foreach ($removedTableNames as $removedTableName) {
                    $finalRemovedTableNames[] = $ormDbName.'.'.$removedTableName;
                }

                foreach ($ormTablesNames as $ormTableName) {
                    if (in_array($ormTableName, $fixtureModelNames)) {

                        $ormFieldNames = array_keys($rawOrmSchema[$ormDbName][$ormTableName]);
                        $fixtureFieldNames = $schemaDefinition->getModelFieldNames($ormDbName, $ormTableName);

                        $addedFieldNames = array_diff($ormFieldNames, $fixtureFieldNames);
                        foreach ($addedFieldNames as $addedFieldName) {
                            $finalAddedFieldNames[] = $ormDbName.'.'.$ormTableName.'.'.$addedFieldName;
                        }

                        $removedFieldNames = array_diff($fixtureFieldNames, $ormFieldNames);
                        foreach ($removedFieldNames as $removedFieldName) {
                            $finalRemovedFieldNames[] = $ormDbName.'.'.$ormTableName.'.'.$removedFieldName;
                        }
                    }
                }
            }
        }

        return array(
            'added_databases'   => $finalAddedDatabaseNames,
            'removed_databases' => $finalRemovedDatabaseNames,
            'added_tables'      => $finalAddedTableNames,
            'removed_tables'     => $finalRemovedTableNames,
            'added_fields'      => $finalAddedFieldNames,
            'removed_fields'    => $finalRemovedFieldNames
        );

    }

}