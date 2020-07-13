<?php


/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class MigrationLoader {
    protected $dbManager;
    protected $lastMigration;
    protected $inlineMigrations;
    protected $fileMigrations;
    protected $dbMigrations;
    

    public function __construct(MigrationManager $dbManager) {
        $this->dbManager     = $dbManager;
        $this->lastMigration = false;
        $this->dbMigrations  = $this->dbManager->getLoaded();
        $this->scanInlineMigrations();
        $this->scanMigrations();
    }

    /**
     * @return mixed
     */
    public function getInlineMigrations($newest=null) {
        if ($newest) {
            $migrations = [];
            foreach ($this->inlineMigrations as $number => $inlineMigration) {
                if (!array_key_exists($number, $this->dbMigrations)){
                    $migrations[$number] = $inlineMigration;
                }
            }

            return $migrations;
        }

        return $this->inlineMigrations;
    }

    /**
     * @param null $newest
     *
     * @return array|false
     */
    public function getFileMigrations($newest=null) {
        if ($newest) {
            $migrations = [];
            foreach ($this->fileMigrations as $number => $fileMigration) {
                if (!array_key_exists($number, $this->dbMigrations)){
                    $migrations[$number] = $fileMigration;
                }
            }

            return $migrations;
        }
        return $this->fileMigrations;
    }

    /**
     * @param array|false $fileMigrations
     * @return MigrationLoader
     */
    public function setFileMigrations($fileMigrations)
    {
        $this->fileMigrations = $fileMigrations;
        return $this;
    }

    public function  getLast() {
        if (false === $this->lastMigration) {
            try{
                $this->lastMigration = $this->dbManager->getLast();
            }catch (TableNotFoundException $exception) {
                $this->dbManager->createTableIfNotExists();
                $this->lastMigration = $this->dbManager->getLast();
            }
            if(empty($this->lastMigration)) {
                $this->lastMigration = 0;
            }
        }

        return $this->lastMigration;
    }

    public function getNext() {
        $last = $this->getLast();
        $last = empty($last) ? 0 : $last['number'];
        $migrations = array_map(function ($path){
            return (int) substr($path, -8, 4);
        }, $this->scanMigrations());

        $migrations[] = $last;
        $next = max($migrations) + 1;

        return [
            'number'   => sprintf('%04d', $next),
            'status'   => null,
            'executed' => null,
        ];
    }

    public function addNext() {
        $next = $this->getNext();
        file_put_contents(
            __DIR__.'/migration_'.$next['number'].'.php',
            '<?php'.PHP_EOL.PHP_EOL.'return '.var_export($next, true).';'
        );
    }

    public function saveInFile($path, $migration) {
        file_put_contents(
            __DIR__.'/'.$path,
            '<?php'.PHP_EOL.PHP_EOL.'return '.var_export($migration, true).';'.PHP_EOL
        );
    }

    /**
     * @return mixed
     */
    public function scanInlineMigrations() {
        $this->inlineMigrations = [];

        foreach (get_class_methods($this) as $file) {
            if (preg_match('`^up\_(\d{4})$`', $file, $matches)) {

                $this->inlineMigrations[(int) $matches[1]] = $matches[0];
            }
        }

        return $this;
    }

    public function scanMigrations() {
        $this->fileMigrations = [];

        foreach (scandir(__DIR__) as $file) {
            if (preg_match('`^migration\_(\d{4})\.php$`', $file, $matches)) {

                $this->fileMigrations[(int) $matches[1]] = $matches[0];
            }
        }

        return $this;
    }

    public function loadMigrations($notLoaded=null) {
        set_time_limit(1200);
        $migrationsFile = $this->getFileMigrations($notLoaded);
        $migrationsInline = $this->getInlineMigrations($notLoaded);
        $migrations = array_unique(array_merge(
            array_keys($migrationsInline),
            array_keys($migrationsFile)
        ));
        sort($migrations);

        $allMigrations = [];
        $loaded = array_map(function ($migration) {
            return $migration['number'];
        }, $this->dbMigrations);

        foreach ($migrations as $number) {
            if ($notLoaded && in_array($number, $loaded)) {
                continue;
            }
            if(array_key_exists($number, $migrationsInline)) {
                $allMigrations[] = $migrationsInline[$number];
            }
            if(array_key_exists($number, $migrationsFile)) {
                $allMigrations[] = $migrationsFile[$number];
            }
        }

        return $allMigrations;
    }

    public function up() {
        $this->getLast();
        foreach ($this->loadMigrations(true) as $migration) {
            $logs = [];
            $isOK = false;
            $isCallable = true;
            $details    = [];
            if(0 === strpos($migration, 'up')){
                $nbr     = substr($migration, 3);
                $details = ['number' => $nbr, 'is_callable' => $isCallable];

            }elseif(0 === strpos($migration, 'migration')){
                $details['number'] = (int) substr($migration, 10, 4);
                $migration = require_once $migration;
                $isCallable = false;
                $migration['is_callable'] = $isCallable;
            }

            if($isCallable){
                $isOK = $this->{$migration}($logs, $details);
                $migration = $details;
            }else{
                if(!empty($migration['continue_if_error'])) {
                    $isOK = $this->dbManager->executeOneByOne($migration['requests'], $logs, !empty($migration['ignore_if_error'] )? $migration['ignore_if_error'] : []);
                } else {
                    $isOK = $this->dbManager->executeRequests($migration['requests'], $logs, []);
                }
            }

            if ($isOK || !empty($migration['always_ok'])) {
                $migration['status'] = 0;
            } else {
                $migration['status'] = 1;
            }
            $migration['logs'] = $logs;
            if (empty($migration['in_progress'])) {
                $this->dbManager->add($migration);
            }
            $migration['current']  = true;

            return $migration;
        }

        return false;
    }

    public function up_0007(&$logs, &$migration) {
        $migration['description'] = 'Populates TABLES subsidiary and bank.';
        $tables = [
            'alert',
            'category',
            'freq_of_hits_per_cust_category',
            'freq_of_hits_per_scenario',
            'scenario',
            'seuil',
            'variables',
            ];
        $rows = [];
        $rowsVerification = [];
        foreach ($tables as $table) {
            try {
                foreach ($this->dbManager->readSubsidiaries($table) as $row) {
                    $bankId = $row['bankid'];
                    if(preg_match('`^\d+$`', $bankId)) {
                        $bankId = (int) $bankId;
                    }
                    if(
                        !array_key_exists(strtolower($bankId), $rowsVerification) ||
                        !in_array(strtolower($row['subsidiary']), $rowsVerification[strtolower($bankId)])
                    ) {
                        $rowsVerification[strtolower($bankId)][] = strtolower($row['subsidiary']);
                        $rows[$bankId][] = $row['subsidiary'];
                    }
                }
            } catch (Exception $exception) {
                $logs[] = $exception->getMessage();
            }
        }

        $subsidiaryManager = new SubsidiaryManager;
        $bankManager = new BankManager;
        $banks = array_keys($rows);
        sort($banks);
        if (!in_array('', $banks, true)) {
            array_unshift($banks, '', 0, 1);
        }
        $banks = array_unique($banks);
        foreach ($banks as $name) {
            $bankManager->add($name, null);
        }
        $banks = $bankManager->getAllNamed();

        foreach ($rows as $bank => $subsidiaryRows) {
            foreach ($subsidiaryRows as $subsidiary) {
                $subsidiaryManager->add($subsidiary, $banks[$bank]);
            }
        }

        return true;
    }

    public function up_0008(&$logs, &$migration) {
        $migration['description'] = 'Factorization of subsidiary and bankid';
        $tables = [
            'alert',
            'category',
            'freq_of_hits_per_cust_category',
            'freq_of_hits_per_scenario',
            'scenario',
            'seuil',
            'variables',
            ];

        foreach ($tables as $table) {
            $query = 'UPDATE `'.$table.'` SET `subsidiary_id` = ? WHERE `subsidiary` = ?;';
            $this->dbManager->loadSubsidiaries();
            foreach ($this->dbManager->loadSubsidiaries() as $subsidiary => $subsidiaryId) {
                try {
                    $this->dbManager->execute($query, 'ds', [$subsidiaryId, $subsidiary]);

                }catch (Exception $exception){
                    $logs[] = $table . ': ' . $exception->getMessage();
                }
            }

            $query = 'SELECT COUNT(*) FROM `'.$table.'` WHERE subsidiary_id IS NULL OR subsidiary_id = 0';

            $count = false;
            try {
                $count = $this->dbManager->fetchAssoc($query);

            }catch (Exception $exception) {
                $logs[] = $table . ': ' . $exception->getMessage();
            }

            if (is_array($count) && array_key_exists(0, $count) && 0 === $count[0]) {
                $query = 'ALTER TABLE `'.$table.'` DROP INDEX `subsidiary`;';
                try {
                    $this->dbManager->execute($query);
                } catch (Exception $exception) {
                    $logs[] = $table . ', INDEX : ' . $exception->getMessage();
                }

                $query = 'ALTER TABLE `'.$table.'` DROP `subsidiary`;';
                try {
                    $this->dbManager->execute($query);
                } catch (Exception $exception) {
                    $logs[] = $table.', DROP subsidiary: '.$exception->getMessage();
                }
            }

        }

        return true;
    }

    public function up_0022(&$logs, &$migration) {
        $migration['description'] = 'Factorization of subsidiary and bankid';
        $tables = [
            'alert',
            'category',
            'freq_of_hits_per_cust_category',
            'freq_of_hits_per_scenario',
            'scenario',
            'seuil',
            'variables',
            ];

        foreach ($tables as $table) {
            $query = 'SELECT COUNT(*) FROM `'.$table.'` WHERE subsidiary_id IS NULL OR subsidiary_id = 0';

            try {
                $count = $this->dbManager->fetchOneAssoc($query);
                $count = is_array($count) ? array_shift($count) : $count;

            }catch (Exception $exception) {
                $logs[] = $table . ': ' . $exception->getMessage();
            }

            if (empty($count)) {
                $query = 'ALTER TABLE `'.$table.'` DROP INDEX `subsidiary`;';
                $errors = null;
                try {
                    $this->dbManager->execute($query, '', [], $errors);
                    if($errors){
                        $logs = array_merge($logs, $errors);
                    }

                } catch (Exception $exception) {
                    $logs[] = $table . ', INDEX : ' . $exception->getMessage();
                }

                $query = 'ALTER TABLE `'.$table.'` DROP `subsidiary`;';
                $errors = null;
                try {
                    $this->dbManager->execute($query, '', [], $errors);
                    if($errors){
                        $logs = array_merge($logs, $errors);
                    }
                } catch (Exception $exception) {
                    $logs[] = $table.', DROP subsidiary: '.$exception->getMessage();
                }
            }

        }

        return true;
    }

    public function up_0028(&$logs, &$migration) {
        $scenariosManager = new ScenariosManager();

        $migration['description'] = 'Scenarios names unification.';
        $fields = ['base_name', 'version_level1', 'version_level2', 'version_level3'];

        $scenarios = $scenariosManager->getAll();
        foreach ($scenarios as $id => $scenario) {
            $values = ScenariosManager::splitName($scenario);
            $data = array_combine(array_slice($fields, 0, count($values)), $values);
            $data['slug'] = implode('', $values);

            $error = [];
            $scenariosManager->updateWith($data, $id, false, $error);
            if (!empty($error)) {
                if (is_array($error)) {
                    $error = [$error];
                }
                $logs = array_merge($logs, $error);
            }
        }

        return 0 == count($logs);
    }

    public function up_0029(&$logs, &$migration) {
        $migration['description'] = 'freq_of_hits_per_scenario, add field scenario_id';

        $query = 'ALTER TABLE `freq_of_hits_per_scenario`
                   CHANGE `id_alert` `id` INT(11) NOT NULL AUTO_INCREMENT,
                   ADD `scenario_id` INT NULL AFTER `scenario`,
                   ADD KEY `FK_foh_scenarios` (`scenario_id`);
                   ALTER TABLE `freq_of_hits_per_scenario`
                   ADD CONSTRAINT `FK_foh_scenarios` FOREIGN KEY (`scenario_id`) REFERENCES `scenarios` (`id`);
';
        $errors = [];
        $warnings = [];
        try {
            $this->dbManager->execute($query, '', [], $errors);
            if($errors){
                $warnings = array_merge($warnings, $errors);
            }

        } catch (Exception $exception) {
            $warnings[] = 'freq_of_hits_per_scenario : ' . $exception->getMessage();
        }

        $query = 'ALTER TABLE `freq_of_hits_per_scenario` ADD `is_migrated` TINYINT(3) NULL DEFAULT NULL;';
        $errors = null;
        try {
            $this->dbManager->execute($query, '', [], $errors);
            if($errors){
                $warnings = array_merge($logs, $errors);
            }

        } catch (Exception $exception) {
            $warnings[] = 'freq_of_hits_per_scenario : ' . $exception->getMessage();
        }

        $scenariosManager = new ScenariosManager();
        $scenarios = $scenariosManager->getAll();
        $slugs     = $scenariosManager->getAllSlugs();

        $manager = new FreqHitsScenarioManager();

        $query = 'SELECT * FROM `freq_of_hits_per_scenario` WHERE scenario_id IS NULL AND is_migrated IS NULL LIMIT 100';
        $rows  = $this->dbManager->fetchAssoc($query);
        while (!empty($rows)) {
            foreach ($rows as $row){
                $id = $this->getScenarioMatches($scenarios, $slugs, $row['scenario']);
                if ($id) {
                    $manager->updateWith(['scenario_id' => $id, 'is_migrated' => 1], $row['id'], true, null);
                } else {
                    $manager->updateWith(['is_migrated' => 1], $row['id'], true, null);
                }
            }
            $rows  = $this->dbManager->fetchAssoc($query);
        }

        $return = 0 == count($logs) && empty($rows);
        $logs = array_merge($warnings, $logs);

        return $return;
    }

    public function up_0031(&$logs, &$migration) {
        $bankManager = new BankManager();

        $migration['description'] = 'alert, add field is_migrated';

        $query = 'ALTER TABLE `bank` ADD `code_devise` VARCHAR(6) NULL AFTER `zone_id`, ADD `devise_label` VARCHAR(50) NULL AFTER `code_devise`, ADD `tuax_euro` FLOAT NULL AFTER `devise_label`;';
        $errors = null;
        try {
            $this->dbManager->execute($query, '', [], $errors);
            if($errors){
                $logs = array_merge($logs, $errors);
            }

        } catch (Exception $exception) {
            $logs[] = 'Bank : ' . $exception->getMessage();
            return false;
        }

        $query = 'UPDATE `bank` SET `tuax_euro` = 1 ;';
        $errors = null;
        try {
            $this->dbManager->execute($query, '', [], $errors);
            if($errors){
                $logs = array_merge($logs, $errors);
            }

        } catch (Exception $exception) {
            $logs[] = 'bank : ' . $exception->getMessage();
        }

        $query = 'UPDATE `bank` SET `tuax_euro` = 1 ;';
        $errors = null;
        try {
            $this->dbManager->execute($query, '', [], $errors);
            if($errors){
                $logs = array_merge($logs, $errors);
            }

        } catch (Exception $exception) {
            $logs[] = 'bank : ' . $exception->getMessage();
        }

        return 0 == count($logs);
    }

    public function up_0043(&$logs, &$migration) {
        $scenariosManager = new ScenariosManager();

        $migration['description'] = 'alert, scenarios in joined table <small>(ass_scen_1, ...ass_scen_50).</small>';

        $query = 'ALTER TABLE `alert` ADD `is_migrated` TINYINT(3) NULL DEFAULT NULL AFTER `ass_scenario_50`;';
        $errors = null;
        try {
            $this->dbManager->execute($query, '', [], $errors);
            if($errors){
                $logs = array_merge($logs, $errors);
            }

        } catch (Exception $exception) {
            $logs[] = 'alert : ' . $exception->getMessage();
        }

        $query = 'ALTER TABLE `alert_scenarios` ADD UNIQUE `IDX_ass_alert_unique` (`alert_id`, `scenario_id`, `number`);';
        $errors = null;
        try {
            $this->dbManager->execute($query, '', [], $errors);
            if($errors){
                $logs = array_merge($logs, $errors);
            }

        } catch (Exception $exception) {
            $logs[] = 'alert_scenarios : ' . $exception->getMessage();
        }

        $m = [];
        $this->up_0031($logs, $m);

        return true;
    }
    public function up_0044(&$logs, &$migration) {
        $migration['description'] = 'alert, scenarios in joined table <small>(ass_scen_1, ...ass_scen_50).</small>';
        $errors = [];

        $scenariosManager = new ScenariosManager();
        $manager = new AlertManager();
        $reader  = new AlertReader([]);

        $query = 'SELECT * FROM `alert` WHERE is_migrated IS NULL LIMIT 100';
        $rows  = $this->dbManager->fetchAssoc($query);

        $i = 0;
        $time = time();
        while (!empty($rows)) {
            foreach ($rows as $row){
                $row = $reader->mapScenarios($row, $scenariosManager);
                if (!empty($row['scenario_ids'])) {
                    $manager->deleteScenarios($row['id_alert']);
                    $manager->addScenarios($row['scenario_ids'], $row['id_alert']);
                } else {
                    $manager->setAlertMigrated($row['id_alert']);
                }
            }
            $i += 100;
            if ($i >= 5000) {
                $migration['in_progress'] = true;
                break;
            }
            $error = [];
            $rows = $this->dbManager->fetchAssoc($query, '', [], $errors);
            if (!empty($error)) {
                $errors += $error;
                break;
            }
        }
        $t = time() - $time;
        $session = Session::start();
        $message = '<b>'.$migration['description'].'</b><br>'.number_format($i, 0, '', ' ').
            ' Alert Migrated At once, in '.sprintf('%02d:%02d:%02d (%d seconds)', ($t/3600),($t/60%60), $t%60, $t);
        if (empty($error) && $i) {
            $donne = $this->dbManager->fetchOneAssoc('SELECT COUNT(*) AS \'count\' FROM `alert` WHERE `is_migrated` = 1');
            $total = $this->dbManager->fetchOneAssoc('SELECT COUNT(*) AS \'count\' FROM `alert`');
            $message .= '<br>'.sprintf('<b>%s</b> / <b>%s</b>',
                    number_format((int) $donne['count'], 0, '', ' '),
                    number_format((int) $total['count'], 0, '', ' ')
                );
            $donne = 100 * $donne['count'] / $total['count'];
            $session->addMessageFlash(['progress' => $donne, 'refresh' => 1.5], 'progress', 1.5);
            $session->addMessageFlash($message, 'info');
            if ($donne != $total) return false; // IS Running...
        }
        $session->addMessageFlash($message, 'info');

        return 0 == count($logs);
    }

    public function up_0045(&$logs, &$migration) {
        $migration['description'] = 'Add admin_users flag';
        $errors = [];
        $manager = new UserManager();

        $query = 'ALTER TABLE `table_utilisateur` ADD `admin_users` TINYINT(3) NOT NULL DEFAULT 0 AFTER `datechg`;';
        $errors = null;
        try {
            $this->dbManager->execute($query, '', [], $errors);
            if($errors){
                $logs = array_merge($logs, $errors);
            }

        } catch (Exception $exception) {
            $logs[] = 'user : ' . $exception->getMessage();
        }


        return 0 == count($logs);
    }

    public function getScenarioMatches($scenarios, $slugs, $subject)
    {
        foreach ($scenarios as $id => $scenario) {
            if (false !== strpos($subject, $scenario)) {
                return $id;
            }
            if (!empty($slugs[$id]) && false !== strpos(str_replace([' ', '_', '-'], '', $subject), $slugs[$id])) {
                return $id;
            }
        }
    }
}
