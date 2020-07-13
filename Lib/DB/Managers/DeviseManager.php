<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class DeviseManager extends DBManager {
    protected $countries = [];
    protected $devises   = [];
    public function __construct() {
        parent::__construct();
        $this->tableName = 'devise_value';
    }

    public function insertData($rows, &$errors=null, &$deletedNumber=0, $columnsConditions=[]) {
        $this->loadCountry();
        for($key=0; $key<count($rows); $key++) {
            if (!array_key_exists($rows[$key]['country_label_en'], $this->countries)) {
                $this->addCountry($rows[$key]['country_label_en'], $rows[$key]['country_label_fr']);
                $this->loadCountry();
            }

            $rows[$key]['country_id'] = $this->countries[$rows[$key]['country_label_en']];

            unset($rows[$key]['country_label_en']);
            unset($rows[$key]['country_label_fr']);

        }

        $this->loadDevises();
        for($key=0; $key<count($rows); $key++) {
            if (!array_key_exists($rows[$key]['code_devise'], $this->devises)) {
                $this->addDevise($rows[$key]['code_devise'], $rows[$key]['label_en'], $rows[$key]['label_fr'], $rows[$key]['code_devise1'] );
            }
            $this->loadDevises();

            $rows[$key]['devise_id'] = $this->devises[$rows[$key]['code_devise']];


            unset($rows[$key]['code_devise']);
            unset($rows[$key]['label_en']);
            unset($rows[$key]['label_fr']);
            unset($rows[$key]['code_devise1']);

        }

        if ('DD-MM-YYYY' == Configuration::get('deviseDateFormat')) {
            for($key=0; $key<count($rows); $key++) {
                $date = explode('-', $rows[$key]['date_devise']);
                $rows[$key]['date_devise'] = $date[2].'-'.$date[1].'-'.$date[0];
            }
        }

        $deleteRow = [];
        foreach ($rows as $row) {
            $deleteRow[$row['devise_id']][] = $row['date_devise'];
        }

        if (!empty($deleteRow)) {
            $deletedNumber += $this->deleteDevise($deleteRow);
        }

        return $this->doInsertData($rows, $errors);
    }


    public function addCountry($name, $nameFr) {
        $query = 'INSERT INTO `country` (`name`, `name_fr`) VALUES (?, ?);';

        $this->execute($query, 'ss', [$name, $nameFr]);
    }

    public function loadCountry() {
        $query = 'SELECT * FROM `country`';
        $this->countries = [];
        foreach ($this->fetchAssoc($query) as $row) {
            $this->countries[$row['name']] = $row['id'];
        }
    }

    public function addDevise($code, $label, $labelFr, $code1) {
        $query = 'INSERT INTO `devise` (`code`, `label`, `label_fr`, `code_devise1`) VALUES (?, ?, ?, ?);';

        $this->execute($query, 'ss', [$code, $label, $labelFr, $code1]);
    }

    public function deleteDevise($rows) {
        $conditions = [];
        foreach ($rows as $deviseId => $dateDevise) {
            $dateDevise = array_map(
                function ($item){
                    return "'$item'";
                },
                $dateDevise
            );
            $dateDevise = implode(',', $dateDevise);
            $conditions[] = "(`devise_id` = $deviseId AND `date_devise` IN ($dateDevise)) ";
        }
        $query = 'DELETE FROM `devise_value` WHERE '.implode(' OR ', $conditions);
        $this->execute($query);

        return $this->db->affectedRows();
    }

    public function loadDevises() {
        $query = 'SELECT * FROM `devise`';
        $this->devises = [];
        foreach ($this->fetchAssoc($query) as $item) {
            $this->devises[$item['code']] = $item['id'];
        }
    }



    public function doInsertData($rows, &$erros=null) {
        $results = [];
        if (empty($rows)) {
            return $results;
        }

        $i=1;
        $errnos = [];
        foreach ($rows as $data) {
            $i++;
            try {
                $result = $this->query($this->getInsertQuery($data), $erros, $errnos, $i);
            } catch (\Exception $exception) {
                ddd($this->getInsertQuery($data));
                $results[] = [
                'error' => "Line $i: " . $exception->getMessage(),
                    'row' => $data,
                ];
                continue;
            }
            if ($result) {
                $results[] = [
                    'error' => false,
                ];
            } else {
                $results[] = [
                    'error' => "Line $i: " . $this->db->error,
                    'row' => $data,
                ];
            }
        }

        return $results;
    }
}
