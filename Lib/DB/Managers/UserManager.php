<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class UserManager extends DBManager {
    const DEFAULT_PASS = 'sgabs_sacr';
    const USER_PASS_SALT = '$6$rounds=5000$usesomesillystringforsalt$';
    public function __construct() {
        parent::__construct();
        $this->tableName = 'table_utilisateur';
    }

    public static function encrypt($passe) {
        return crypt($passe, self::USER_PASS_SALT);
    }

    public function getAll() {
        $types      = '';
        $parameters = [];
        $conditions = [];

        $query =
            'SELECT * '.
            "FROM `{$this->tableName}` ";

        return $this->fetchAssoc($query, $types, $parameters);
    }

    public function isUerAdmin($user) {
        $types      = '';
        $parameters = [];
        $conditions = [];

        $query =
            'SELECT COUNT(*) AS \'count\' '.
            "FROM `{$this->tableName}` ".
            "WHERE user = '$user' AND admin_users = 1 ";

        $count = $this->fetchOneAssoc($query, $types, $parameters);

        return !empty($count['count']);
    }
}
