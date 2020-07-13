<?php

/**
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

abstract class DBManager
{
    /** @var PDOMapper|MysqliMapper $db */
    protected $db;
    protected $tableName;
    protected $subsidiaries;
    protected $banks;
    protected $scenarios;
    protected $scenariosSlugs;


    public function getDB($id) {
        return $this->db;
    }

    /**
     * @return mixed
     */
    public function getScenariosSlugs()
    {
        return $this->scenariosSlugs;
    }
    protected $hasSubsidiary = true;
    protected $hasBank = false;
    protected $bankField = 'bank';
    protected $preserveFields = [];
    protected $hasScenario = false;

    public function __construct()
    {
        $this->db = Configuration::getConnection();
    }

    protected function extractScenarioName($start=12, $fieldName='') {
        $scenarioFormula = 'SUBSTR(
                    scenario,
                    1,
                    INSTR(scenario, \':\') -1)';

        if ($start){
            $scenarioFormula = str_replace(
                'scenario',
                str_replace('?', $start, 'SUBSTR(scenario, ?)'),
                $scenarioFormula
            );
        }

        if ($fieldName){
            $scenarioFormula = str_replace('scenario', $fieldName, $scenarioFormula);
        }

        return $scenarioFormula;
    }

    public function execute($query, $types='', $parameters=[], &$errors=null) {
        return $this->db->execute($query, $types, $parameters, $errors);
    }

    public function fetchAssoc($query, $types='', $parameters=[], $errors=false) {
        return $this->db->fetchAssoc($query, $types, $parameters, $errors);
    }

    public function fetchOneAssoc($query, $types='', $parameters=[]) {
        return $this->db->fetchOneAssoc($query, $types, $parameters);
    }

    public function deleteData($subsidiary, $period) {
        $this->execute("DELETE FROM `{$this->tableName}` WHERE subsidiary_id = '{$subsidiary}' and period = '{$period}'");
        $deletedNumber = $this->db->affectedRows();
        $this->query("DELETE FROM `{$this->tableName}` WHERE period NOT LIKE '20%'");
        $deletedNumber += $this->db->affectedRows();

        return $deletedNumber;
    }

    public function deleteDataId($subsidiary, $period) {
        $this->query("DELETE FROM `{$this->tableName}` WHERE subsidiary_id = '{$subsidiary}' and period = '{$period}'");
        $deletedNumber = $this->db->affectedRows();
        $this->query("DELETE FROM `{$this->tableName}` WHERE period NOT LIKE '20%'");
        $deletedNumber += $this->db->affectedRows();

        return $deletedNumber;
    }

    public function deleteDataBy($ArrayConditions=[]) {
        $conditions = [];
        $parameters = [];
        $types = '';
        $query = "DELETE FROM `{$this->tableName}` ";
        foreach ($ArrayConditions as $left => $right) {
            $type = 's';
            if(is_int($right)){
                $type = 'd';
            }
            $this->setConditionEqual($types, $conditions, $parameters, $left, $right, $type);
        }
        if(!empty($conditions)){
            $query .= 'WHERE '.implode(' AND ', $conditions);
        }

        $this->execute($query, $types, $parameters);

        return $this->db->affectedRows();
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
            $scenarioIds = false;
            if (array_key_exists('scenario_ids', $data)){
                $scenarioIds = $data['scenario_ids'];
                unset($data['scenario_ids']);
            }
            $result = $this->query($this->getInsertQuery($data), $erros, $errnos, $i);
            if ($result && !is_object($results) && $scenarioIds) {
                $this->addScenarios($scenarioIds, $result);
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

    public function getInsertQuery($data) {
        $data = array_map('addslashes', $data);
        $request = "INSERT INTO `{$this->tableName}` (";
        $request .= implode(', ', array_map(function ($key){
            return "`{$key}`";
        }, array_keys($data)));
        $request .= ')'.PHP_EOL.' VALUES '.PHP_EOL.'(';
        $request .= implode(', ', array_map(function ($value){
            return "'{$value}'";
        }, $data));
        $request .= ')';

        return $request;
    }

    protected function query($query, &$errors=null, &$errnos=null, $line=false){
        $line = $line ? "&nbsp;&nbsp;&nbsp;&nbsp; File Line: $line" : '';

        return $this->db->execute($query, '', [], $errors,$errnos, $line);
    }

    protected function fetchAray($result){

        return array_map('array_shift', mysqli_fetch_all($result));
    }

    public function bindParam(mysqli_stmt $statement, $types, $args) {
        $args = array_slice(func_get_args(), 1);
        $statement = $statement->bind_param($args);
        if (!$statement) {
            throw new RuntimeException(str_replace(['; ', 'near '], [';<br>', 'near:<br>'], $this->db->error));
        }

        return $statement;
    }

    public function addConditionEqual(&$types, &$conditions, &$parameters, $left, $right, $type='s') {
        $conditions[] = $left.' = ?';
        $parameters[] = $right;
        $types       .= $type;

        return $this;
    }

    public function addConditionNotEqual(&$types, &$conditions, &$parameters, $left, $right, $type='s') {
        $conditions[] = $left.' != ?';
        $parameters[] = $right;
        $types       .= $type;

        return $this;
    }

    public function addConditionLT(&$types, &$conditions, &$parameters, $left, $right, $type='d') {
        $conditions[] = $left.' >= ?';
        $parameters[] = $right;
        $types       .= $type;

        return $this;
    }

    public function addConditionGT(&$types, &$conditions, &$parameters, $left, $right, $type='d') {
        $conditions[] = $left.' <= ?';
        $parameters[] = $right;
        $types       .= $type;

        return $this;
    }

    public function addConditionIN(&$types, &$conditions, &$parameters, $left, $right, $type='s') {
        if (!is_array($right)) {
            return $this->setConditionEqual($types, $conditions, $parameters, $left, $right, $type);
        }

        $conditions[] = $left.' IN (' . implode(', ', array_fill(0, count($right), '?')) . ')';
        foreach ($right as $param) {
            $parameters[] = $param;
            $types .= $type;
        }

        return $this;
    }

    public function addConditionLike(&$types, &$conditions, &$parameters, $left, $right) {
        $conditions[] = "$left LIKE '%?%' ";
        $parameters[] = $right;
        $types .= 's';

        return $this;
    }

    public function setConditionLike(&$types, &$conditions, &$parameters, $left, $right) {
        $conditions[] = "$left LIKE '%$right%' ";

        return $this;
    }

    public function addConditionNotIN(&$types, &$conditions, &$parameters, $left, $right, $type='s') {
        if (!is_array($right)) {
            return $this->setConditionNotEqual($types, $conditions, $parameters, $left, $right, $type);
        }

        $conditions[] = $left.' NOT IN (' . implode(', ', array_fill(0, count($right), '?')) . ')';
        foreach ($right as $param) {
            $parameters[] = $param;
            $types .= $type;
        }

        return $this;
    }

    public function setConditionNotIN(&$types, &$conditions, &$parameters, $left, $right, $type='s') {
        if (!is_array($right)) {
            return $this->setConditionNotEqual($types, $conditions, $parameters, $left, $right, $type);
        }
        $right = array_map(function ($item) {
            return '\''.$item.'\'';
        }, $right);

        $conditions[] = $left.' NOT IN (' . implode(', ', $right) . ')';

        return $this;
    }

    public function addConditionBetween(&$types, &$conditions, &$parameters, $left, $rightMin, $rightMax, $type='ss') {
        $conditions[] = $left.' BETWEEN ? AND ?';
        $parameters[] = $rightMin;
        $parameters[] = $rightMax;
        $types       .= $type;

        return $this;
    }

    public function addConditionBetweenFields(&$types, &$conditions, &$parameters, $left, $rightMin, $rightMax, $type='s') {
        $conditions[] = "? BETWEEN $rightMin AND $rightMax";
        $parameters[] = $left;
        $types       .= $type;

        return $this;
    }

    public function setConditionBetween(&$types, &$conditions, &$parameters, $left, $rightMin, $rightMax, $type='ss') {
        $conditions[] = $left.' BETWEEN \''.$rightMin.'\' AND \''.$rightMax.'\'';

        return $this;
    }

    public function setConditionIN(&$types, &$conditions, &$parameters, $left, $right, $type='s') {
        if (!is_array($right)) {
            return $this->setConditionEqual($types, $conditions, $parameters, $left, $right, $type);
        }

        $right = array_map(function ($statement) use ($type) {
            if ('d' == $type){
                return $statement;
            }

            return '\''.$statement.'\'';
        }, $right);

        $conditions[] = $left.' IN (' . implode(', ', $right) . ')';

        return $this;
    }

    public function setConditionLIKES(&$types, &$conditions, &$parameters, $left, $right, $type='s') {
        if (!is_array($right)) {
            return $this->setConditionLike($types, $conditions, $parameters, $left, $right, $type);
        }

        $right = array_map(function ($statement) use ($left) {
            return $left.' LIKE \'%'.str_replace(['.', ' '], '_', $statement).'%\'';
        }, $right);

        $conditions[] = '(' . implode(' OR ', $right) . ')';

        return $this;
    }

    public function setConditionNotLIKES(&$types, &$conditions, &$parameters, $left, $right, $type='s') {
        if (!is_array($right)) {
            return $this->setConditionLike($types, $conditions, $parameters, $left, $right, $type);
        }

        $right = array_map(function ($statement) use ($left) {
            return $left.' NOT LIKE \'%'.str_replace(['.', ' '], '_', $statement).'%\'';
        }, $right);

        $conditions[] = '(' . implode(' OR ', $right) . ')';

        return $this;
    }

    public function setConditionEqual(&$types, &$conditions, &$parameters, $left, $right, $type='s') {
        $conditions[] = $type == 'd' ? ($left.' = '.$right) : ($left.' = \''.$right.'\'');

        return $this;
    }

    public function setConditionNotEqual(&$types, &$conditions, &$parameters, $left, $right, $type='s') {
        $conditions[] = $type == 'd' ? ($left.' != '.$right) : ($left.' != \''.$right.'\'');

        return $this;
    }

    public function setConditionLT(&$types, &$conditions, &$parameters, $left, $right, $type='d') {
        $conditions[] = $left.' >= \''.$right.'\'';

        return $this;
    }

    public function setConditionGT(&$types, &$conditions, &$parameters, $left, $right, $type='d') {
        $conditions[] = $left.' <= \''.$right.'\'';

        return $this;
    }
    public function get($id) {
        $types      = '';
        $parameters = [];
        $query =
            'SELECT * '.
            'FROM `'.$this->tableName.'` '.
            'WHERE id = '.$id;

        return $this->fetchOneAssoc($query, $types, $parameters);
    }

    public function getBUs() {
        return [
            'afmo' => 'AFMO',
            'eurruss' => 'EUR RUSS',
        ];
    }
    public function getZones($BUs=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT DISTINCT name, id '.
            'FROM `zone` ';

        if (!empty($BUs)) {
            $this->setConditionIN($types, $conditions, $parameters, 'bu', $BUs);
            $query .= 'WHERE '.implode(' AND ', $conditions).' ';
        }
        $query .= 'ORDER BY name ASC;';

        $rows = [];
        foreach ($this->fetchAssoc($query, $types, $parameters) as $row) {
            $rows[$row['id']] = utf8_encode($row['name']);
        }

        return $rows;
    }
    public function getCountries() {
        $query =
            'SELECT * '.
            'FROM `country` '.
            'ORDER BY name ASC;';

        $rows = [];
        foreach ($this->fetchAssoc($query) as $row) {
            $rows[$row['id']] = utf8_encode($row['name']);
        }

        return $rows;
    }

    public function getBanks($zones=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT DISTINCT b.name AS name, b.id AS id '.
            'FROM `bank` b '.
            'LEFT JOIN `zone` AS z ON b.zone_id = z.id ';
        if (!empty($zones)) {
            $this->setConditionIN($types, $conditions, $parameters, 'zone_id', $zones, 'd');
            $query .= 'WHERE ('.implode(' AND ', $conditions).' OR zone_id IS NULL AND zone_id < 5 ) ';
        }
        $query .= 'ORDER BY b.name ASC;';

        $rows = [];
        foreach ($this->fetchAssoc($query, $types, $parameters) as $row) {
            $rows[$row['id']] = utf8_encode($row['name']);
        }

        return $rows;
    }

    public function getSubsidiaries($zones=false, $banks=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT DISTINCT s.name AS name, s.id AS id '.
            "FROM `{$this->tableName}` t ".
            'LEFT JOIN `subsidiary` AS s ON t.subsidiary_id = s.id '.
            'LEFT JOIN `zone` AS z ON s.zone_id = z.id '.
            'WHERE s.enabled = 1 ';

        if (!empty($zones)) {
            $this->setConditionIN($types, $conditions, $parameters, 'z.id', $zones, 'd');
        }
        if (!empty($banks)) {
            $this->setConditionIN($types, $conditions, $parameters, 's.bank_id', $banks, 'd');
        }

        if (!empty($conditions)) {
            $query .= 'AND '.implode(' AND ', $conditions).' ';
        }

        $query .= 'ORDER BY s.name ASC;';

        $rows = [];
        foreach ($this->fetchAssoc($query, $types, $parameters) as $row) {
            $rows[$row['id']] = $row['name'];
        }

        return $rows;
    }

    public function getEnds($subsidiaries=false, $start='') {
        $conditions = [];
        $params = [];
        $types  = '';
        $query = "SELECT DISTINCT `period` FROM {$this->tableName} WHERE subsidiary_id != 0 AND period LIKE '20%' ";

        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $params, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($start)) {
            if (is_array($start)) {
                $start = min($start);
            }
            $this->setConditionLT($types, $conditions, $params, 'period', $start, 's');
        }

        $query .= count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '';
        $query .= ' ORDER BY `period` ASC';

        $periods = array_column($this->fetchAssoc($query, $types, $params), 'period');

        return array_combine($periods, $periods);
    }

    public function getPeriods($subsidiaries=false) {
        $conditions = [];
        $params = [];
        $types  = '';
        $query = "SELECT DISTINCT `period` FROM {$this->tableName} WHERE subsidiary_id != 0 AND period LIKE '20%' ";

        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $params, 'subsidiary_id', $subsidiaries, 'd');
        }
        $query .= count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '';
        $query .= ' ORDER BY `period` ASC';

        $periods = array_column($this->fetchAssoc($query, $types, $params), 'period');

        return array_combine($periods, $periods);
    }

    public function loadSubsidiaries() {
        $this->subsidiaries = [];
        foreach ($this->fetchAssoc("SELECT * FROM `subsidiary` ORDER BY name ASC;") as $row) {
            $this->subsidiaries[strtolower($row['name'])] = $row['id'];
        }

        return $this->subsidiaries;
    }

    public function loadBanks() {
        $this->subsidiaries = [];
        foreach ($this->fetchAssoc("SELECT * FROM `bank` ORDER BY name ASC;") as $row) {
            $this->banks[$row['name']] = $row['id'];
        }

        return $this->banks;
    }

    public function loadScenarios() {
        $query = "SELECT *, LENGTH(name) AS priority FROM `scenarios` ORDER BY priority DESC;";
        $this->scenarios = [];
        foreach ($this->fetchAssoc($query) as $row) {
            $this->scenarios[$row['name']] = $row['id'];
            $this->scenariosSlugs[strtolower(str_replace([' ', '_', ], '', $row['slug']))] = $row['id'];
        }

        return $this->scenarios;
    }
    public function getSubsidiaryId($row) {
        $subsidiary = $row['subsidiary'];
        if(!array_key_exists(strtolower($subsidiary), $this->subsidiaries)) {
            $this->loadBanks();
            $bankId = $this->getBankId($row);
            $query = "INSERT INTO `subsidiary` (`name`, `bank_id`) VALUES (?, ?);";
            $this->execute($query, 'sd', [$subsidiary, $bankId]);

            $this->loadSubsidiaries();
        }

        return $this->subsidiaries[strtolower($subsidiary)];
    }

    public function getBankId($row, $index='bankid') {
        $bankId = $row[$index];

        if(!array_key_exists($bankId, $this->banks)) {
            if (empty($bankId)){
                foreach ($this->banks as $id => $bank) {
                    $weight = str_replace([' ','0'], '', $id);
                    if (empty($weight)) {
                        return $bank;
                    }
                }
            }
            try {

                $bankId = !empty($row['bank']) ? $row['bank'] : null;
                $query = "INSERT INTO `bank` (`name`) VALUES (?);";
                $this->execute($query, 's', [$bankId]);
            }catch (\Exception $exception){
            }
            $this->loadBanks();
        }
        if (empty($bankId)){
            if(array_key_exists($bankId, $this->banks)) {
                return $this->banks[$bankId];
            }elseif (array_key_exists('', $this->banks)){
                return $this->banks[''];
            }elseif (array_key_exists(0, $this->banks)){
                return $this->banks[0];
            }else{
                $banks = $this->banks;

                return array_shift($banks);
            }
        }

        return  $this->banks[$bankId];
    }

    public function matchScenario($reference)
    {
        $row      = explode(':', $reference);
        $scenario = $row[1];
        if (0 !== strpos('BHFM', $scenario) || empty($scenario) || strlen($scenario) > 20) {
            $scenario = false;
            $matches = [];
            preg_match('`BHFM(?: )?(?:_)?[0-9].`', $reference,$matches);
            if (!empty($matches[0])){
                $scenario = $matches[0];
            }
        }

        return $scenario;
    }

    public function getScenarioId($row, $index='scen_name', $errors=null) {
        if (is_array($row) && $index === 'scen_name' && !array_key_exists('scen_name', $row) && array_key_exists('scenario', $row)){
            $index = 'scenario';
        }

        $reference = is_array($row) ? $row[$index] : $row;

        foreach ($this->scenarios as $scenarioDB => $scenarioId) {
            if (false !== strpos($reference, $scenarioDB)){
                return $scenarioId;
            }
        }

        foreach ($this->scenariosSlugs as $scenarioDB => $scenarioId) {
            if (false !== strpos($reference, $scenarioDB)){
                return $scenarioId;
            }
        }

        $name = $this->matchScenario($reference);
        if (empty($name)) {
            return null;
        }

        foreach ($this->scenarios as $scenarioDB => $scenarioId) {
            if ($name == $scenarioDB) {
                return $scenarioId;
            }
        }

        foreach ($this->scenariosSlugs as $scenarioDB => $scenarioId) {
            if ($name == $scenarioDB) {
                return $scenarioId;
            }
        }

        $slug = strtolower(str_replace(['_', ' '], '', $name));
        if(array_key_exists($slug, $this->scenariosSlugs)) {
            return $this->scenariosSlugs[$slug];
        }

        if (is_array($row)) {
            $lonName = explode(':', $reference);
            $lonName = explode('/', $lonName[1]);
            $descriptionEn = !empty($lonName[0]) ? $lonName[0] : false;
            $descriptionFr = !empty($lonName[1]) ? $lonName[1] : $descriptionEn;

            $query = "INSERT INTO `scenarios` (`name`, `score`, `description_en`, `description_fr`, `comment`) VALUES (?, ?, ?, ?, ?);";
            $this->execute($query, 'dsdsss', [$name, $row['scen_score'], $descriptionEn, $descriptionFr, $row['scen_comment']], $errors);
            $this->loadScenarios();

            return $this->scenarios[$name];
        }

        return  false;
    }

    public function guessScenarioId($name, $en, $fr, $errors=null, $reference) {
        if (empty($name) ||  empty($reference) || false === strpos($reference, 'BHFM')) {
            return null;
        }
        if(!array_key_exists($name, $this->scenarios)) {
            $slug = strtolower(str_replace(['_', ' '], '', $name));
            if(array_key_exists($slug, $this->scenariosSlugs)) {
                return $this->scenariosSlugs[$slug];
            }

            if (strpos($reference, 'BHFM')) {
                return null;
            }

            $query = "INSERT INTO `scenarios` (`name`, `description_en`, `description_fr`) VALUES (?, ?, ?);";
            $this->execute($query, 's', [$name, $en, $fr], $errors);
            $this->loadScenarios();
        }

        return $this->scenarios[$name];
    }

    public function getScenarios($row, $index='scenario_name') {
        $bankId = $row[$index];
        if (0 > $row[$index]) {
            return null;
        }
        if(!array_key_exists($bankId, $this->banks)) {
            $bankId = !empty($row['bank']) ? $row['bank'] : null;
            $query = "INSERT INTO `bank` (`name`) VALUES (?);";
            $statement = $this->execute($query, 's', $bankId);

            $statement->execute();
            $this->loadBanks();
        }

        return $this->banks[$bankId];
    }
    public function mapToId($rows, $field='subsidiary', $index=false, &$errors=false) {
        $newRows = [];
        if('subsidiary' == $field){
            if (false === $index){
                $index = 'subsidiary_id';
            }
            $this->loadSubsidiaries();
            foreach ($rows as $row) {
                $row[$index] = $this->getSubsidiaryId($row);
                if ('subsidiary' != $index){
                    unset($row['subsidiary']);
                }
                $newRows[] = $row;
            }
        }elseif('bank' == $field) {
            if (false === $index){
                $index = 'bank_id';
            }
            $this->loadBanks();
            foreach ($rows as $row) {
                $row[$index] = $this->getBankId($row);
                if ('bank' != $index && !in_array('bank', $this->preserveFields)){
                    unset($row['bank']);
                }
                if ('bankid' != $index && !in_array('bankid', $this->preserveFields)){
                    unset($row['bankid']);
                }
                if ('bank_id' != $index && !in_array('bank_id', $this->preserveFields)){
                    unset($row['bank_id']);
                }
                $newRows[] = $row;
            }
        }elseif('scenario' == $field) {
            $this->loadScenarios();
            foreach ($rows as $row) {
                $row['scenario_id'] = $this->getScenarioId($row);
                // unset($row['scenario']);
                $newRows[] = $row;
            }
        }

        return $newRows;
    }

    public function insertData($rows, &$errors=null, &$deletedNumber=0, $columnsConditions=[]) {
        if($this->hasSubsidiary){
            $rows = $this->mapToId($rows);
        }
        if ($this->hasBank){
            $rows = $this->mapToId($rows, 'bank', $this->bankField);
        }

        if ($this->hasScenario){
            $rows = $this->mapToId($rows, 'scenario', $errors);
        }
        if (0 === count($columnsConditions) && $this->hasSubsidiary){
            if(array_key_exists('subsidiary_id', $rows[0])) {
                $deletedNumber = $this->deleteDataId($rows[0]['subsidiary_id'], $rows[0]['period']);
            } else {
                $deletedNumber = $this->deleteData($rows[0]['subsidiary'], $rows[0]['period']);
            }
        }elseif(count($columnsConditions)){
            $columnsConditions = array_intersect_key($rows[0], array_combine($columnsConditions, $columnsConditions));
            $deletedNumber = $this->deleteDataBy($columnsConditions);
        }

        return $this->doInsertData($rows, $errors);
    }

    public function all($subsidiaries=false, $periods=false, $start=0, $limit=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT c.*, s.name AS subsidiary '.
            'FROM `'.$this->tableName.'` AS c '.
            'LEFT JOIN `subsidiary` AS s ON c.subsidiary_id = s.id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') ';

        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }


        $query .= count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '';
        if ($limit){
            $query .= ' LIMIT '.$start.', '.$limit;
        }

        return $this->fetchAssoc($query, $types, $parameters);
    }

    public function count($subsidiaries=false, $periods=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT COUNT(*) AS total '.
            'FROM `'.$this->tableName.'` AS c '.
            'LEFT JOIN `subsidiary` AS s ON c.subsidiary_id = s.id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') ';

        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }


        $query .= count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '';

        $results = $this->fetchOneAssoc($query, $types, $parameters);

        return !empty($results['total']) ? (int) $results['total'] : 0;
    }

    public function getAllWithFlagNotN($subsidiaries=false, $periods=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT c.*, s.name AS subsidiary '.
            'FROM `'.$this->tableName.'` AS c '.
            'LEFT JOIN `subsidiary` AS s ON c.subsidiary_id = s.id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') '.
            'AND update_flag != \'N\' ';



        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }


        $query .= count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '';

        return $this->fetchAssoc($query, $types, $parameters);
    }

    public function getSubsidiariesWithFlagNotN($zones=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT DISTINCT s.name AS name, s.id AS id '.
            "FROM `{$this->tableName}` t ".
            'LEFT JOIN `subsidiary` AS s ON t.subsidiary_id = s.id '.
            'LEFT JOIN `zone` AS z ON s.zone_id = z.id '.
            'WHERE s.enabled = 1 AND update_flag != \'N\' ';

        if (!empty($zones)) {
            $this->setConditionIN($types, $conditions, $parameters, 'z.id', $zones, 'd');
            $query .= 'AND '.implode(' AND ', $conditions).' ';
        }

        $query .= 'ORDER BY s.name ASC;';

        $rows = [];
        foreach ($this->fetchAssoc($query, $types, $parameters) as $row) {
            $rows[$row['id']] = $row['name'];
        }

        return $rows;
    }

    public function getPeriodsWithFlagNotN($subsidiary=false) {
        $conditions = [];
        $params = [];
        $types  = '';
        $query = "SELECT DISTINCT `period` FROM {$this->tableName} WHERE subsidiary_id != 0 AND period LIKE '20%' AND update_flag != 'N' ";

        if ($subsidiary) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiary, 'd');
            $query .= 'AND '.implode(' AND ', $conditions).' ';
            // $params[] = $subsidiary;
            $types .= 'd';
        }
        $query .= 'ORDER BY `period` ASC';

        return array_column($this->fetchAssoc($query, $types, $params), 'period');
    }

    public function updateWith($data, $id=false, $preparedQuery=true, $errors=null, $idName='id') {
        if (!empty($data['id'])) {
            if (empty($id)) {
                $id = $data['id'];
            }
            unset($data['id']);
        }

        if(empty($id) || empty($data)) {
            return false;
        }

        $query = "UPDATE `{$this->tableName}` SET ";
        if ($preparedQuery) {
            $fieldsSet = [];
            $params    = [];
            $types     = '';
            foreach ($data as $field => $value) {
                $fieldsSet[] = "$field = ?";
                $params[] = utf8_decode($value);
                $types .= 's';
            }

            $query .= implode(', ', $fieldsSet).' WHERE '.$idName.' = ? ';
            $params[] = $id;
            $types .= 'd';

            return $this->execute($query, $types, $params, $errors);
        }

        $fieldsSet = [];
        foreach ($data as $field => $value) {
            $fieldsSet[] = "$field = '".addcslashes(utf8_decode($value), '\'')."'";
        }

        $query .= implode(', ', $fieldsSet).' WHERE id = '.$id;

        return $this->execute($query);
    }

    public function insertWith($data, $preparedQuery=true, $errors=null) {
        if(empty($data)) {
            return false;
        }

        $query = "INSERT INTO `{$this->tableName}` ";
        if ($preparedQuery) {
            $fields = [];
            $params = [];
            $types  = '';
            foreach ($data as $field => $value) {
                $fields[] = "`$field`";
                $params[] = $value;
                $types .= 's';
            }

            $query .= '('.implode(', ', $fields).') VALUES ('.implode(', ', array_fill(0, count($fields), '?')).');';
            $types .= 'd';

            return $this->execute($query, $types, $params, $errors);
        }

        $fieldsSet = [];
        foreach ($data as $field => $value) {
            $fieldsSet[] = "$field = '".addcslashes(utf8_decode($value), '\'')."'";
        }

        return $this->execute($query);
    }
    public function getAllWithFields() {
        $query =
            "SELECT * ".
            "FROM `{$this->table}` ";

        $query .= 'ORDER BY `name` ';

        $rows = [];
        foreach ($this->fetchAssoc($query) as $row) {
            $rows[$row['id']] = $row;
        }

        return $rows;
    }

    public function getAllCategories($enabled = null, $exclude=[]) {
        $conditions = [];
        $params = [];
        $types  = '';
        $query  =
            'SELECT DISTINCT category_name, category_id, r_valid_to '.
            'FROM category ';
        if (!empty($exclude)) {
            $this->setConditionNotLIKES($types, $conditions, $parameters, 'category_name', $exclude, 's');
            $query .= 'WHERE '.implode(' AND ', $conditions).' ';
        }

        $query .= 'ORDER BY category_id ASC';

        $rows = [];
        foreach ($this->fetchAssoc($query, $types, $params) as $row) {
            if (is_bool($enabled)){
                if(empty($row['r_valid_to']) && !$enabled){
                    continue;
                }elseif(!empty($row['r_valid_to'])){
                    if ($enabled !== DateTime::createFromFormat('YmdHis', $row['r_valid_to']) > new DateTime()) {
                        continue;
                    }
                }
            }

            $category = explode('/', $row['category_name']);
            $category = trim($category[0]);
            $rows[$row['category_id']] = $category;
        }

        return $rows;
    }

    /**
     * @return string
     */
    public function getUniqueCategory($category)
    {
        $category = trim($category);
        if (false !== strpos($category, 'TOUS CLIENTS')){
            return 'ALL CUSTOMERS';
        }

        return $category;
    }

    public function addScenarios($data, $id) {
        foreach ($data as $item) {
            $query = "INSERT INTO `alert_scenarios` (`alert_id`, `scenario_id`, `number`, `reference`) VALUES (?, ?, ?, ?);";
            $errors = [];
            $this->db->execute($query, 'ddds', [$id, $item['id'], $item['number'], $item['reference']],$errors);
        }

        $scenariosFields = array_map(function ($scenario) {
            return "`ass_scenario_".$scenario."` = ''";
        }, range(1, 50));
        $query = "UPDATE `alert` SET ".implode(', ', $scenariosFields).", `is_migrated` = '1' WHERE `alert`.`id_alert` = $id;";
        $errors = [];
        $this->db->execute($query, [], [], $errors);
    }

    public function deleteScenarios($id) {
        $query = "DELETE FROM `alert_scenarios` WHERE alert_id = ".$id;
        return $this->db->execute($query, [], [], $errors);
    }

    public function setAlertMigrated($id) {
        $query = "UPDATE `alert` SET `is_migrated` = '1' WHERE `alert`.`id_alert` = $id;";
        return $this->db->execute($query, [], [], $errors);
    }

    public function rtrimZero($value) {
        return false !== strpos($value,'.') ? rtrim(rtrim($value,'0'),'.') : $value;
    }
}
