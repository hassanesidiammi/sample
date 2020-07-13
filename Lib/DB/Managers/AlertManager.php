<?php

/**
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class AlertManager extends DBManager {
    const STATUS_CLOSED          = 'CLOSED';
    const STATUS_CLOSED_WITH_SAR = 'CLOSED with SAR';

    const STATUS_UNWORKED   = 'UNWORKED';
    const STATUS_IN_PROCESS = 'IN PROCESS';

    static $statusTreated   = [self::STATUS_CLOSED,   self::STATUS_CLOSED_WITH_SAR];
    static $statusUntreated = [self::STATUS_UNWORKED, self::STATUS_IN_PROCESS];

    protected $bankField = 'bankid';

    public function __construct() {
        parent::__construct();
        $this->tableName = 'alert';
    }


    public static function alertsStatus() {
        return [
            1 => self::STATUS_IN_PROCESS,
            2 => self::STATUS_CLOSED,
            3 => self::STATUS_CLOSED_WITH_SAR,
            4 => self::STATUS_UNWORKED,
        ];
    }
    public static function alertsStatusConfig() {
        return [
            'MONITORING TREATED' => [
                'status' => AlertManager::$statusTreated,
                'title'  => 'Monitoring < 10 pts Treated',
            ],
            'MONITORING UNTREATED' => [
                'status' => AlertManager::$statusUntreated,
                'title'  => 'Monitoring < 10 pts Untreated',
            ],
            'REMINDERS TREATED' => [
                'status'       => AlertManager::$statusTreated,
                'title'        => 'REMINDERS < 10 pts Treated',
                'monitoringYN' => false,
                'reminderYN'   =>  'Y',
            ],
            'REMINDERS UNTREATED' => [
                'status'       => AlertManager::$statusUntreated,
                'title'        => 'REMINDERS < 10 pts Untreated',
                'monitoringYN' => false,
                'reminderYN'   =>  'Y',
            ],
            'TECHNICALS ALERT TREATED' => [
                'status'        => AlertManager::$statusTreated,
                'title'        => 'TECHNICALS ALERT TREATED',
                'monitoringYN'  => 'Y',
                'reminderYN'    => 'Y',
                'maxTotalScore' => 0,
            ],
            'TECHNICALS ALERT UNTREATED' => [
                'status' => AlertManager::$statusUntreated,
                'title'        => 'TECHNICALS ALERT UNTREATED',
                'monitoringYN'  => 'Y',
                'reminderYN'    => 'Y',
                'maxTotalScore' => 0,
            ],
            'CLOSED' => [
                'status' => 'CLOSED',
                'title'        => 'CLOSED > 10pts',
                'monitoringYN'  => false,
                'maxTotalScore' => false,
                'minTotalScore' => 10,
            ],
            'UNWORKED' => [
                'status' => 'UNWORKED',
                'title'  => 'UNWORKED > 10pts',
                'monitoringYN' => false,
                'maxTotalScore' => false,
                'minTotalScore' => 10,
            ],
            'IN PROCESS' => [
                'status' => 'IN PROCESS',
                'title'  => 'IN PROCESS > 10pts',
                'monitoringYN' => false,
                'maxTotalScore' => false,
                'minTotalScore' => 10,
            ],
            'CLOSED with SAR' => [
                'status' => 'CLOSED with SAR',
                'title'        => 'CLOSED with SAR > 10pts',
                'monitoringYN' => false,
                'maxTotalScore' => false,
                'minTotalScore' => 10,
            ],
        ];
    }

    public static function statusTiles(){
        $rows = [];
        foreach (self::alertsStatusConfig() as $status => $row) {
            $rows[$status] = $row['title'];
        }

        return $rows;
    }

    public function IgnoreIndex()
    {
        try {
            // return $this->query("ALTER IGNORE TABLE {$this->tableName} ADD UNIQUE INDEX(subsidiary, hitid);");
        } catch (Exception $e) {
            return $e;
        }
    }

    public function countAlertsByStatus($maxTotalScore, $minTotalScore, $start, $end, $alertStatus=null) {
        if (null === $alertStatus) {
            $alertStatus = self::$statusTreated;
        }
        $alertStatus = implode(', ', array_map(function($status){
            return "'{$status}'";
        }, $alertStatus));
        $query = 'SELECT alert_status, count(*) AS number, MIN(period) as \'start\', MAX(period) as \'end\' FROM alert WHERE '.
            'subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') AND '.
            'monitoring_yn = \'Y\' AND '.
            'total_score < ? AND '.
            'alert_status IN ('.$alertStatus.') '.
            'alert_status = "MONITORING TREATED" AND '.
            'DATE_FORMAT(period, "%Y%m") BETWEEN ? AND ? '.
            'GROUP BY alert_status';

        return $this->db->fetchAssoc($query,'dss', [$maxTotalScore, $start, $end]);
    }

    protected function alertsStatusConditions($subsidiaries, $status, $start, $end, $monitoringYN='Y', $reminderYN=false, $maxTotalScore=10, $minTotalScore=false){
        $conditions   = [];
        $parameters   = [];
        $types        = '';

        if (!empty($monitoringYN)) {
            $this->setConditionIN($types, $conditions, $parameters, 'monitoring_yn', $monitoringYN);
        }

        if (!empty($reminderYN)) {
            $this->setConditionIN($types, $conditions, $parameters, 'reminder_yn', $reminderYN);
        }

        if (0 === $maxTotalScore){
            $conditions[] = 'total_score = 0';
        } elseif ($maxTotalScore){
            $conditions[] = "total_score < $maxTotalScore ";
        }
        if($minTotalScore){
            $conditions[] = "total_score >= $minTotalScore " ;
        }

        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }else{
            $conditions[] = 'subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).')';
        }

        if (is_array($start)) {
            $start = min($start);
        }

        if (is_array($end)) {
            $end = max($end);
        }

        if (!empty($start) && !empty($end)){
            $this->setConditionBetween($types, $conditions, $parameters, 'SUBSTR(period, 1, 6)', $start, $end);
        } elseif (!empty($start)) {
            $this->setConditionLT($types, $conditions, $parameters, 'SUBSTR(period, 1, 6)', $start, 's');
        }elseif (!empty($end)) {
            $this->setConditionGT($types, $conditions, $parameters, 'SUBSTR(period, 1, 6)', $end, 's');
        }

        if (is_string($status)) {
            $status = [$status];
        }

        $this->setConditionIN($types, $conditions, $parameters, 'alert_status', $status);

        return ['conditions' => $conditions, 'parameters' => $parameters, 'types' => $types];
    }
    public function countAlertsStatus($subsidiary, $status, $start, $end, $monitoringYN='Y', $reminderYN=false, $maxTotalScore=10, $minTotalScore=false) {
        $conditions = $this->alertsStatusConditions($subsidiary, $status, $start, $end, $monitoringYN, $reminderYN, $maxTotalScore, $minTotalScore);
        $query = 'SELECT count(*) AS \'count\' FROM alert WHERE '.implode(' AND ', $conditions['conditions']);

        $rows = $this->fetchAssoc($query, $conditions['types'], $conditions['parameters']);

        if (array_key_exists(0, $rows)) {
            $rows = $rows[0];
        }

        return !empty($rows['count']) ? $rows['count'] : 0;
    }

    public function alertsStatusPeriods($subsidiary, $start, $end) {
        $types = '';
        $conditions = [];
        $parameters = [];
        $query = 'SELECT DISTINCT(period) FROM '.$this->tableName.' WHERE ';

        $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiary, 'd');
        $this->setConditionBetween($types, $conditions, $parameters, 'period', $start, $end);
        $query .= implode(' AND ', $conditions).' ORDER BY period ASC;';

        return array_column($this->fetchAssoc($query, $types, [$subsidiary,$start, $end]), 'period');
    }

    public function alertsStatusCounts($subsidiary, $start, $end) {
        $counts = [];
        foreach (self::alertsStatusConfig() as $key => $config) {
            $monitoringYN  = array_key_exists('monitoringYN', $config) ? $config['monitoringYN'] : 'Y';
            $reminderYN    = array_key_exists('reminderYN',   $config) ? $config['reminderYN']   : false;
            $maxTotalScore = $minTotalScore = false;
            if(array_key_exists('maxTotalScore',  $config)) {
                if($config['maxTotalScore']) {
                    $maxTotalScore = $config['maxTotalScore'];
                } elseif(0 === $config['maxTotalScore']) {
                    $maxTotalScore = 0;
                }

                if(array_key_exists('minTotalScore',  $config) && $config['minTotalScore']) {
                    $minTotalScore = $config['minTotalScore'];
                }
            }
            $counts[$key] = [
                'subsidiary' => $this->countAlertsStatus(
                    $subsidiary, $config['status'], $start, $end, $monitoringYN, $reminderYN, $maxTotalScore, $minTotalScore
                ),
                'all' => $this->countAlertsStatus(
                    false, $config['status'], $start, $end, $monitoringYN, $reminderYN, $maxTotalScore, $minTotalScore
                ),
            ];
        }

        return $counts;
    }

    public function countAverageSubsidiary($subsidiaries=false, $periods=false) {
        return $this->_countAverageSubsidiary($subsidiaries, $periods, 'DISTINCT(a.id_alert)', false);
    }

    public function countAverageSubsidiaryClosed($subsidiaries=false, $periods=false) {
        return $this->_countAverageSubsidiary($subsidiaries, $periods, 'DISTINCT(a.id_alert)', AlertManager::$statusTreated);
    }

    public function countAverageSubsidiaryNewest($subsidiaries=false, $periods=false) {
        // $status = [self::STATUS_UNWORKED => self::STATUS_UNWORKED];
        $status = false;
        return $this->_countAverageSubsidiary($subsidiaries, $periods, 'DISTINCT(a.id_alert)', $status);
    }

    public function countAverageSubsidiarySAR($subsidiaries=false, $periods=false) {
        return $this->_countAverageSubsidiary($subsidiaries, $periods, 'DISTINCT(a.id_alert)', [self::STATUS_CLOSED_WITH_SAR => self::STATUS_CLOSED_WITH_SAR]);
    }

    public function countDaysLatest($subsidiaries=false, $periods=false, $scenariosJ) {
        return $this->_countAverageSubsidiary($subsidiaries, $periods, 'distinct(`lastentrydate`)', false);
    }

    public function getPeriodDays($subsidiaries=false, $periods=false, $context='scoringdate') {
        if (!is_array($subsidiaries)) {
            $subsidiaries = [$subsidiaries];
        }
        $subsidiaries = array_filter($subsidiaries);
        if (!is_array($periods)) {
            $periods = [$periods];
        }
        $periods = array_filter($periods);

        $conditions   = [];
        $parameters   = [];
        $types        = '';

        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }

        if (!empty($status)) {
            $this->setConditionIN($types, $conditions, $parameters, 'alert_status', $status);
        }

        $query =
            'SELECT DISTINCT a.'.$context.' AS \'day\', s.name AS subsidiary, a.period AS period '.
            'FROM alert AS a '.
            'LEFT JOIN subsidiary AS s ON a.subsidiary_id = s.id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') '.
            'AND `period` LIKE \'20%\' '.(count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '').' '.
            'GROUP BY `subsidiary_id`, lastentrydate '.
            'ORDER BY subsidiary_id, day ASC '
        ;

        $rows = [];
        foreach ($this->fetchAssoc($query, $types, $parameters) as $row) {
            $rows[$row['subsidiary']][substr($row['day'], 0, 6)][] = $row['day'];
        }

        return $rows;
    }

    public function getEmptyDaysLatest($daysLatest) {
        $emptDays = [];
        foreach ($daysLatest as $subsidiary => $row) {
            foreach ($row as $period => $days) {
                $firstDay = date_create(substr($period, 0, 4).'-'.substr($period, 4, 2).'-01');
                foreach (range(1, (int) $firstDay->format('t')) as $day) {
                    $day = sprintf($period.'%02d', $day);
                    if(!in_array($day, $days)) {
                        $dayDate = date_create($day);
                        $emptyDays[$subsidiary][$period][$day] = $dayDate->format('w');
                    }
                }
            }
        }

        return $emptyDays;
    }

    protected function  _countAverageSubsidiary($subsidiaries=false, $periods=false, $context='*', $status=false) {
        if (!is_array($subsidiaries)) {
            $subsidiaries = [$subsidiaries];
        }
        $subsidiaries = array_filter($subsidiaries);
        if (!is_array($periods)) {
            $periods = [$periods];
        }
        $periods = array_filter($periods);

        $conditions   = [];
        $parameters   = [];
        $types        = '';

        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries);
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }

        if (!empty($status)) {
            $this->setConditionIN($types, $conditions, $parameters, 'alert_status', $status);
        }

        $scenariosFrequency = '';

        if (false && !empty($scenariosJ)) {
            $this->setConditionIN($types, $conditions, $parameters, 'ass.scenario_id', array_keys($scenariosJ));
        }

        $query =
            'SELECT s.`name` AS subsidiary, a.`period` AS period, c.frequency AS frequency, '.$scenariosFrequency.' COUNT('.$context.') AS \'count\' '.
            'FROM alert AS a '.
            'LEFT JOIN subsidiary AS s ON a.subsidiary_id = s.id '.
            'INNER JOIN alert_scenarios AS ass ON a.id_alert = ass.alert_id '.
            'LEFT JOIN scenarios AS c ON c.id = ass.scenario_id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') '.
            'AND c.enabled = 1 '.
            'AND `period` LIKE \'20%\' '.(count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '').' '.
            'GROUP BY `subsidiary_id`';
        ;

        $rows = [];
        foreach ($this->fetchAssoc($query.', frequency;', $types, $parameters) as $row) {
            $rows[$row['subsidiary']][$row['frequency']] = $row['count'];
        }
        $totals = [];
        foreach ($this->fetchAssoc($query, $types, $parameters) as $row) {
            $totals[$row['subsidiary']] = $row['count'];
        }

        return [$rows, $totals];
    }

    public function countAlertsScore($subsidiary=false, $period=false) {
        $conditions = [];
        $parameters = [];
        $types      = '';

        if (!empty($subsidiary)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiary, 'd');
        }
        if (!empty($period)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $period);
        }

        $query  =
            'SELECT s.name AS subsidiary, IF(total_score >= 50, 50, FLOOR(total_score / 10) * 10) AS score_range, COUNT(*) as number '.
            'FROM alert AS a '.
            'LEFT JOIN subsidiary AS s ON a.subsidiary_id = s.id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') ';
        if (count($conditions)) {
            $query .=('AND '.implode(' AND ', $conditions)).' ';
        }
        $query .= 'GROUP BY s.name, score_range ';

        $rows = [];
        foreach ($this->fetchAssoc($query, $types, $parameters) as $row) {
            $rows[$row['subsidiary']][(string) $row['score_range']] = $row['number'];
        }

        return array_map(function ($row) {
            $row['total'] = array_sum($row);

            return $row;
        }, $rows);
    }

    public function countAverageScore($subsidiary=false, $period, $score=0) {
        $conditions = [];
        $parameters = [];
        $types      = '';

        $query =
            'SELECT a.period, s.name AS subsidiary, s.id AS subsidiary_id, a.customerid, a.category_name, MAX(CONVERT(a.total_score, SIGNED INTEGER)) AS count '.
            'FROM alert AS a '.
            'LEFT JOIN subsidiary AS s ON a.subsidiary_id = s.id '.
            'WHERE s.id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') '
        ;

        if (!empty($subsidiary)) {
            $this->setConditionIN($types, $conditions, $parameters, 's.id', $subsidiary, 'd');
        }

        if (!empty($period)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $period);
        }

        if($conditions) {
            $query .= ' AND '.implode(' AND ', $conditions).' ';
        }

        $query .=
            'GROUP BY customerid '.
            'HAVING count >= \''.$score.'\' ';
        // $parameters[] = $score;
        $types       .= 'd';

        $query .= 'ORDER BY count desc, period desc, s.name';

        return $this->fetchAssoc($query, $types, $parameters);
    }

    public function countCustomerMaxScore($customerId, $subsidiary, $period) {
        $types = '';
        $parameters = [];
        $query = 'SELECT MAX(CONVERT(total_score, SIGNED INTEGER)) as \'count\' FROM alert WHERE customerid = ? AND subsidiary = ? AND period = ?';
        if($customerId){
            $query .= 'AND customerid = ? ';
            $parameters[] = $customerId;
            $types       .= 's';
        }
        if($subsidiary){
            $query .= 'AND subsidiary_id = ? ';
            $parameters[] = $subsidiary;
            $types       .= 'd';
        }

        if($period){
            $query .= 'AND period = ? ';
            $parameters[] = $period;
            $types       .= 's';
        }

        return $this->fetchOneAssoc($query, $types, $parameters);
    }


    public function countByCustomerPeriodSubsidiary($subsidiary=false, $period, $minNumber=10) {
        $conditions = [];
        $parameters = [];
        $types      = '';

        $query =
            'SELECT a.period, s.name AS subsidiary, s.id AS subsidiary_id, a.customerid, a.category_name, COUNT(*) as \'number\' '.
            'FROM alert AS a '.
            'LEFT JOIN subsidiary AS s ON a.subsidiary_id = s.id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') '
        ;

        if (!empty($subsidiary)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiary, 'd');
        }

        if (!empty($period)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $period);
        }

        if (count($conditions)) {
            $query .=(' AND '.implode(' AND ', $conditions)).' ';
        }

        $query .=
            'GROUP BY customerid, period, subsidiary_id '.
            'HAVING number > '.$minNumber.' ';
        // $parameters[] = $minNumber;
        $types       .= 'd';

        $query .= 'ORDER BY number DESC, period DESC, s.name, customerid';

        return $this->fetchAssoc($query, $types, $parameters);
    }

    public function countCustomerAlerts($customerId, $subsidiary, $period) {
        $types = '';
        $parameters = [];
        $conditions = ['subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).')'];

        $query = 'SELECT COUNT(*) AS number FROM alert WHERE ';

        if (!empty($customerId)) {
            $this->setConditionIN($types, $conditions, $parameters, 'customerid', $customerId);
        }

        if (!empty($subsidiary)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiary, 'd');
        }

        if (!empty($period)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $period);
        }
        $query .= implode(' AND ', $conditions);

        return $this->fetchOneAssoc($query, $types, $parameters);
    }

    public function countCustomerHighestScore($customerId, $subsidiary=false, $period=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $requests   = [];

        $query =
            'SELECT  s_.name AS scenario_name, count(*) AS \'count\' 
            FROM `alert` AS a
            LEFT JOIN `alert_scenarios` AS a_ ON a.id_alert = a_.alert_id
            LEFT JOIN `scenarios` AS s_  ON s_.id = a_.scenario_id 
            WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') AND s_.enabled = 1';

        if($customerId){
            $this->setConditionEqual($types, $conditions, $parameters, 'customerid', $customerId);
        }
        if (!empty($subsidiary)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiary, 'd');
        }

        if (!empty($period)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $period);
        }

        if (count($conditions)) {
            $query .=(' AND '.implode(' AND ', $conditions)).' ';
        }
        $query .= 'GROUP BY scenario_name ';
        $query .= 'ORDER BY scenario_name ASC';

        $rows = [];
        foreach ($this->fetchAssoc($query, $types, $parameters) as $row) {
            $rows[$row['scenario_name']] = $row['count'];
        }

        return $rows;
    }
    public function highestScoreByScenario(&$types, &$parameters, $scenario, $customerId, $subsidiary=false, $period=false) {
        $query =
            'SELECT SUBSTR('.$scenario.',12,7) AS scenario 
            FROM `alert` 
            WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') '.
            'AND ('.$scenario.' LIKE \'%BHFM__:%\' OR '.$scenario.' LIKE \'%BHFM__bis:%\') ';

        if($customerId){
            $query .= 'AND customerid = ? ';
            $parameters[] = $customerId;
            $types       .= 's';
        }
        if($subsidiary){
            $query .= 'AND subsidiary_id = ? ';
            $parameters[] = $subsidiary;
            $types       .= 'd';
        }

        if($period){
            $query .= 'AND period = ? ';
            $parameters[] = $period;
            $types       .= 's';
        }

        return $query;
    }

    public function getScenariosWeight($subsidiaries=false, $periods=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT MAX(CONVERT(`no_of_scenarios`,SIGNED INTEGER)) '.
            'FROM `'.$this->tableName.'` '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') ';

        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }

        if(!empty($conditions)) {
            $query .= 'AND '.implode(' AND ', $conditions).' ';
        }

        if(!empty($conditions)) {
            $query .= 'AND '.implode(' AND ', $conditions).' ';
        }

        $max = $this->fetchRow($query, $types, $parameters);

        return $max[0];
    }

    public function getCategories($subsidiaries=false, $periods=false) {
        $types      = '';
        $parameters = [];
        $requests   = [];
        $conditions = [];
        $query =
            'SELECT SUBSTR(category_name, 1, INSTR(category_name, \'/\') - 1) AS category_name, CONVERT(category_id, SIGNED INTEGER) AS category_id '.
            'FROM `'.$this->tableName.'` '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') AND category_id != 0 ';

        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }elseif (!empty($periodStart) && !empty($periodEnd)) {
            $this->setConditionBetween($types, $conditions, $parameters, 'period', $periodStart, $periodEnd);
        }

        if(!empty($conditions)) {
            $query .= 'AND '.implode(' AND ', $conditions).' ';
        }
        $query .= 'GROUP BY category_id ';
        $query .= 'ORDER BY CONVERT(category_id, SIGNED INTEGER) ';

        $rows = [];
        foreach ($this->fetchAssoc($query, $types, $parameters) as $row) {
            $rows[$row['category_id']] = trim($row['category_name']);
        }

        return $rows;
    }

    public function getScenariosBy($scenarios, $subsidiaries=false, $periodStart=false, $periodEnd=false) {
        $types      = '';
        $parameters = [];
        $requests   = [];
        $conditions = [];
        $query =
            'SELECT DISTINCT c.name as scenario_name, c.id AS scenario_id  ' .
            'FROM `' . $this->tableName . '` AS a ' .
            'LEFT JOIN alert_scenarios AS ass ON a.id_alert = ass.alert_id '.
            'LEFT JOIN scenarios AS c ON ass.scenario_id = c.id '.
            'WHERE subsidiary_id NOT IN (' . implode(', ', SubsidiaryManager::$ignoredSubsidiaries) . ')  ';

        if (!empty($scenarios)) {
            $this->setConditionIN($types, $conditions, $parameters, 'c.id', $scenarios, 'd');
        }
        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'a.subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periodStart) && !empty($periodEnd)) {
            $this->setConditionBetween($types, $conditions, $parameters, 'a.period', $periodStart, $periodEnd);
        }

        if (!empty($conditions)) {
            $query .= 'AND ' . implode(' AND ', $conditions) . ' ';
        }
        $query .= ' ORDER BY scenario_name ';

        $rows = $this->fetchAssoc($query, $types, $parameters);

        return array_combine(array_column($rows, 'scenario_id'), array_column($rows, 'scenario_name'));
    }

    public function countByCategoryScenariosId($scenarios, $subsidiaries=false, $periods=false, $categories=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT COUNT(*) AS \'count\', SUBSTR(category_name, 1,INSTR(category_name, \'/\') - 1) AS category_name, period, c.name AS scenario_name '.
            'FROM `'.$this->tableName.'` AS a '.
            'INNER JOIN alert_scenarios AS ass ON a.id_alert = ass.alert_id '.
            'LEFT  JOIN scenarios AS c ON c.id = ass.scenario_id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') '.
            'AND category_id != 0 '.
            'AND category_name NOT LIKE \'%ALL CUSTOMERS%\' ';


        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }

        if (!empty($categories)) {
            $this->setConditionLIKES($types, $conditions, $parameters, 'category_name', $categories);
        }
        $query .= count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '';
        $query .= ' GROUP BY scenario_name, category_name, period';
        $query .= ' ORDER BY period DESC';

        $counts = [];
        $rows   = $this->fetchAssoc($query, $types, $parameters);
        foreach ($rows as $row) {
            $category = $this->getUniqueCategory($row['category_name']);

            $counts[$row['scenario_name']][$row['period']][$category] = $row['count'];
        }

        return $counts;
    }
    public function countByCategoryScenarioQuery($scenario, $subsidiaries=false, $periods=false, $categories=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT COUNT(*) AS \'count\', category_name, category_id, SUBSTR(`'.$scenario.'`, 12, 7) AS scenario_name '.
            'FROM `'.$this->tableName.'` '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') '.
            'AND (\''.$scenario.'\' LIKE \'%BHFM__:%\' OR '.$scenario.' LIKE \'%BHFM__bis:%\') ';



        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }

        if (!empty($category)) {
            $this->setConditionIN($types, $conditions, $parameters, 'category_name', $category);
        }
        $query .= count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '';
        // $query .= ' GROUP BY category_name, scenario_name';
        $query .= ' ORDER BY category_name DESC';


        $counts = [];
        $rows   = $this->fetchAssoc($query, $types, $parameters);
        foreach ($rows as $row) {
            $scenarioName = $row['scenario_name'];
            if (substr($scenarioName, -1) == ':') {
                $scenarioName = substr($scenario, 0, -1);
            } else if (substr($scenarioName, -4, 1) == ':') {
                $scenarioName = substr($scenarioName, 0, -4);
            }

            $counts[$row['category_name']][$scenarioName] = $row['count'];
        }

        return $counts;
    }

    public function countByCategoryScenario($scenarios, $status, $subsidiaries=false, $periodStart=false, $periodEnd=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT COUNT(*) AS \'count\', SUBSTR(category_name, 1, INSTR(category_name, \'/\') - 1) AS category_name, category_id, c.name AS scenario_name '.
            'FROM `'.$this->tableName.'` AS a '.
            'INNER JOIN alert_scenarios AS ass ON a.id_alert = ass.alert_id '.
            'LEFT JOIN scenarios AS c ON c.id = ass.scenario_id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') AND category_id != 0 ';

        if (!empty($scenarios)) {
            $this->setConditionIN($types, $conditions, $parameters, 'alert_status', $status);
        }
        if (!empty($status)) {
            $this->setConditionIN($types, $conditions, $parameters, 'alert_status', $status);
        }

        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periodStart)  && !empty($periodEnd)) {
            $this->setConditionBetween($types, $conditions, $parameters, 'period', $periodStart, $periodEnd);
        }
        $query .= count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '';
        $query .= ' GROUP BY category_id';
        $query .= ' ORDER BY c.name, category_id DESC';

        $counts = [];
        $rows   = $this->fetchAssoc($query, $types, $parameters);
        foreach ($rows as $row) {
            $category = $this->getUniqueCategory($row['category_name']);
            if (empty($counts[$row['scenario_name']][$category])){
                $counts[$row['scenario_name']][$category] = 0;
            }
            $counts[$row['scenario_name']][$category] += $row['count'];
        }

        return $counts;
    }

    public function countSarByScenarios($scenario, $subsidiaries=false, $periods=false) {
        return $this->_countByScenariosStatus($scenario, $subsidiaries, $periods, self::STATUS_CLOSED_WITH_SAR);
    }
    public function countByScenarios($scenario, $subsidiaries=false, $periods=false) {
        return $this->_countByScenariosStatus($scenario, $subsidiaries, $periods);
    }
    public function _countByScenariosStatus($scenario, $subsidiaries=false, $periods=false, $status=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT COUNT(*) AS \'count\' '.
            'FROM `'.$this->tableName.'` AS a '.
            'INNER JOIN alert_scenarios AS ass ON a.id_alert = ass.alert_id '.
            'LEFT JOIN scenarios AS c ON c.id = ass.scenario_id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') AND category_id != 0 ';

        if (!empty($scenario)) {
            $this->setConditionIN($types, $conditions, $parameters, 'c.id', $scenario, 'd');
        }
        if (!empty($subsidiaries)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }

        if (!empty($status)) {
            $this->setConditionIN($types, $conditions, $parameters, 'alert_status', $status);
        }
        $query .= count($conditions) ? ('AND '.implode(' AND ', $conditions)) : '';

        return $this->fetchOneAssoc($query, $types, $parameters);
    }

    public function getTreatmentDelay($scenarios, $subsidiaries=false, $periods=false, $status=false) {
        $types      = '';
        $parameters = [];
        $conditions = [];
        $query =
            'SELECT period, COUNT(DISTINCT a.id_alert) AS \'no_alerts\', SUM(a.no_of_days) AS no_days, s.name AS subsidiary '.
            'FROM `'.$this->tableName.'` AS a '.

            'INNER JOIN alert_scenarios AS ass ON a.id_alert = ass.alert_id '.
            'LEFT JOIN scenarios AS c ON c.id = ass.scenario_id '.
            'LEFT JOIN subsidiary AS s ON a.subsidiary_id = s.id '.
            'WHERE subsidiary_id NOT IN ('.implode(', ', SubsidiaryManager::$ignoredSubsidiaries).') AND category_id != 0 ';

        if (!empty($scenarios)) {
            $this->setConditionIN($types, $conditions, $parameters, 'c.id', $scenarios, 'd');
        }

        if (!empty($scenarios)) {
            $this->setConditionIN($types, $conditions, $parameters, 'subsidiary_id', $subsidiaries, 'd');
        }

        if (!empty($periods)) {
            $this->setConditionIN($types, $conditions, $parameters, 'period', $periods);
        }

        if (!empty($status)) {
            $this->setConditionIN($types, $conditions, $parameters, 'alert_status', $status);
        }
        $query .= count($conditions) ? ('AND '.implode(' AND ', $conditions)) : ' ';
        $query .= 'GROUP BY period, subsidiary ';

        $rows = [];
        foreach ($this->fetchAssoc($query, $types, $parameters) as $row) {
            $rows[$row['period']][$row['subsidiary']] = !empty($row['no_alerts']) ? $row['no_days']/$row['no_alerts'] : false;
        }

        return $rows;
    }

    public function insertData($rows, &$errors=null, &$deletedNumber=0, $columnsConditions=[]) {
        $rows = $this->mapToId($rows);
        $rows = $this->mapToId($rows, 'bank', $this->bankField);

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
}