<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class Filter {
    protected $dbManager;
    protected $data;
    protected $fields;

    public function __construct($fields) {
        $this->fields = $fields;
    }

    public function handleData()
    {
        foreach ($this->fields as $field) {
            $field = $_POST[$field];
            if($field){
                $selectedZones = [$field => $zones[$filterZone]];
            }else{
                $selectedZones = $zones;
            }
        }
    }

    public function url($page, $module='index', $get = [], $segment=null) {

    }
}