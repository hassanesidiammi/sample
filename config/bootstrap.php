<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

if (! function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if ( !array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if ( !array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}

$config = require_once 'config.php';

if (!empty($config['debug'])) {
    require_once 'bootstrap_debug.php';
}
require_once 'SimpleAutoloader.php';

SimpleAutoloader::Register();

Configuration::setUp(
    $config
);

switch (Configuration::get('database', 'mapper')) {
    case 'MYSQLI':
        Configuration::setConnection(new MysqliMapper(Configuration::get('database')));
        break;
    case 'MYSQLIWITHOUTPARAMS':
        Configuration::setConnection(new MysqliWithoutParamsMapper(Configuration::get('database')));
        break;
    default:
        Configuration::setConnection(new MysqliWithoutParamsMapper(Configuration::get('database')));
}

Configuration::setConnection(new PDOMapper(Configuration::get('database')));
