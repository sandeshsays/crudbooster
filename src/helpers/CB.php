<?php

namespace crocodicstudio\crudbooster\helpers;

use Session;
use Request;
use Schema;
use Cache;
use DB;
use Route;
use Validator;

class CB extends CRUDBooster
{
    //This CB class is for alias of CRUDBooster class


    //alias of echoSelect2Mult
    public function ES2M($values, $table, $id, $name)
    {
        return CRUDBooster::echoSelect2Mult($values, $table, $id, $name);
    }

    public static function listTables()
    {
        $tables = [];
        $multiple_db = config('crudbooster.MULTIPLE_DATABASE_MODULE');
        $multiple_db = ($multiple_db) ? $multiple_db : [];
        $db_database = config('crudbooster.MAIN_DB_DATABASE');
        $db_database_schema = config('crudbooster.MAIN_DB_SCHEMA');

        if ($multiple_db) {
            try {
                $multiple_db[] = config('crudbooster.MAIN_DB_DATABASE');
                $query_table_schema = implode("','", $multiple_db);
                $tables = DB::select("SELECT CONCAT(TABLE_SCHEMA,'.',TABLE_NAME) FROM INFORMATION_SCHEMA.Tables WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA != 'mysql' AND TABLE_SCHEMA != 'performance_schema' AND TABLE_SCHEMA != 'information_schema' AND TABLE_SCHEMA != 'phpmyadmin' AND TABLE_SCHEMA IN ('$query_table_schema')");
            } catch (\Exception $e) {
                $tables = [];
            }
        } else {
            try {
                $tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.Tables WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA = '" . $db_database_schema . "'");
            } catch (\Exception $e) {
                $tables = [];
            }
        }

        return $tables;
    }

    public static function getTableColumns($table)
    {

        //$cols = DB::getSchemaBuilder()->getColumnListing($table);
        $table = CB::parseSqlTable($table);

        $cols = collect(DB::select('SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table', [
            'database' => $table['database'],
            'table' => $table['table'],
        ]))->map(function ($x) {
            return (array) $x;
        })->toArray();

        $result = [];
        $result = $cols;

        $new_result = [];
        foreach ($result as $ro) {
            $new_result[] = $ro['column_name'];
        }

        return $new_result;
    }

    public static function parseSqlTable($table)
    {

        $f = explode('.', $table);

        if (count($f) == 1) {
            return ["table" => $f[0], "database" => config('crudbooster.MAIN_DB_SCHEMA')];
        } elseif (count($f) == 2) {
            return ["database" => $f[0], "table" => $f[1]];
        } elseif (count($f) == 3) {
            return ["table" => $f[0], "schema" => $f[1], "table" => $f[2]];
        }

        return false;
    }
}
