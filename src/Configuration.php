<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class Configuration {
    private static $data;
    /** @var Configuration $config */
    private static $config;
    private static $connection;

    private function __construct($data=[]) {
        self::$data = $data;
    }

    public static function get($name, $index=false){
        if(!array_key_exists($name, self::$data)) {
            throw new VariableNotFoundException([$name, self::$data]);
        }

        if ($index && !is_array($index)) {
            return !empty(self::$data[$name][$index]) ? self::$data[$name][$index] : null;
        }

        return self::$data[$name];
    }

    public static function is($name){
        if(!array_key_exists($name, self::$data)) {
            return false;
        }

        return (bool) self::$data[$name];
    }

    static public function setUp($data) {
        if(!empty($data['baseUrl'])) {
            $data['baseUrl'] = rtrim($data['baseUrl'], '/');
        };
        if(!self::$config || !self::$config instanceof Configuration) {
            self::$config = new self($data);
        }

        return self::$config;
    }

    static public function getConnection() {
        return self::$connection;
    }

    static public function setConnection($connection) {
        self::$connection = $connection;
    }
}