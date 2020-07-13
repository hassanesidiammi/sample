<?php

/**
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class CategoryManager extends DBManager {
    public function __construct() {
        parent::__construct();
        $this->tableName = 'category';
    }

    public function IgnoreIndex() {}
}