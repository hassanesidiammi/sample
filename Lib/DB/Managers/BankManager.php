<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class BankManager extends DBManager {
    public function __construct() {
        parent::__construct();
        $this->tableName = 'bank';
    }

    public function getAll() {
        return $this->fetchAssoc("SELECT * FROM `{$this->tableName}` ORDER BY name ASC;");
    }

    public function getAllWithZone($devise=false) {
        $query =
            "SELECT b.*, z.name AS 'zone', z.id AS zone_id, c.name AS country_name ".
            "FROM `{$this->tableName}` AS b ".
            "LEFT JOIN zone AS z ON b.zone_id = z.id ".
            "LEFT JOIN country      AS c ON c.id = b.country_id ".
            "ORDER BY b.name ASC;";
        $rows = $this->fetchAssoc($query);
        if (false === $devise) {
            return $rows;
        }

        for($i=0; $i<count($rows); $i++) {
            $deviseDetails = [];
            if (!empty($rows[$i]['country_id'])) {
                $query =
                    "SELECT v.value AS devise_value, d.code AS devise_code ".
                    "FROM `devise_value` AS v ".
                    "LEFT JOIN devise   AS d ON v.devise_id  = d.id ".
                    "LEFT JOIN country  AS c ON v.country_id = c.id ".
                    "WHERE v.country_id = ".$rows[$i]['country_id']." ".
                    "ORDER BY v.date_devise DESC LIMIT 1;";
                $deviseDetails = $this->fetchOneAssoc($query);
            }
            $rows[$i]['devise_value'] = !empty($deviseDetails) && !empty($deviseDetails['devise_value']) ? $deviseDetails['devise_value'] : null;
            $rows[$i]['devise_code']  = !empty($deviseDetails) && !empty($deviseDetails['devise_code']) ? $deviseDetails['devise_code']  : null;
        }

        return $rows;
    }

    public function getAllNamed() {
        $rows = [];
        foreach ($this->getAll() as $row) {
            $rows[$row['name']] = $row['id'];
        }

        return $rows;
    }

    public function add($name, $countryId) {
        $query = "INSERT INTO `bank` (`name`, `zone_id`) VALUES (?, ?);";

        return $this->db->execute($query, 'sd', [$name, $countryId]);
    }
}