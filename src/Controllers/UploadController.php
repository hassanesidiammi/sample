<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class UploadController extends BaseController {
    public function __construct($menu) {
        parent::__construct($menu);
        $this->grantAccess();
    }

    public function indexAction() {
        $params = ['dataDeleted' => false];
        $filterTab        = isset($_POST['table']) ? $_POST['table'] : false;
        $filterPeriod     = isset($_POST['period']) ? $_POST['period'] : false;
        $filterSubsidiary = isset($_POST['subsidiary']) ? $_POST['subsidiary'] : false;

        $filterManager = new FilterManager($this->connection);

        if (isset($_POST['submit']) && 'DELETE' === $_POST['submit'] && $filterPeriod) {
            if ($filterManager->deleteFrom($_POST['table'], $filterSubsidiary, $filterPeriod)) {
                $params['dataDeleted'] = true;
                    $params['deleteTab'] = $filterTab;
                $params['deleteSubsidiary'] = $filterSubsidiary;
                $params['deletePeriod'] = $filterPeriod;
                $filterTab = $filterPeriod = $filterSubsidiary = '';
            }
        }

        return array_merge(
            $params,
            [
                'ligneh' => "<tr><td style='background-color: #000000;color:#ffffff;text-align:center'>Total</td>",
                'totalh' => 0,
                'totalv' => 0,
                'chaine' => "",
                'i' => 0,
                'filterManager' => new FilterManager($this->connection),
                'tables' => [
                    'conversion' => 'rate',
                    'freq_of_hits_per_cust_category' => 'freq_of_hits_per_cust_category',
                    'freq_of_hits_per_scenario' => 'freq_of_hits_per_scenario',
                    'alert' => 'alert',
                ],
                'filterTab' => $filterTab,
                'filterPeriod' => $filterPeriod,
                'filterSubsidiary' => $filterSubsidiary,
            ]
        );
    }

    public function alertAction() {
        $alertReader  = new AlertReader(Configuration::get('alertFieldsColumn'));
        $alertManager = new AlertManager();

        if (!$this->checkFilename(Configuration::get('alertFileRegex'))) {
            return [];
        }
        
        $alertRows = $this->importFromFile($alertReader, $_FILES['filename']['tmp_name'], $_FILES['filename']['name'], new ScenariosManager());

        if(!empty($alertRows)){
            $this->importToDB(
                $alertRows,
                $alertManager,
                'Alert \'s data was imported successfully!',
                'alert'
            );
        }

        return [];
    }


    public function freqHitsCategoryAction() {
        $params = [
            'viewName' => 'freq_hits_category.php',
        ];
        $freqHitsCategoryReader  = new FileReader(Configuration::get('freqHitsCategoryFieldsColumn'));
        $freqHitsCategoryManager = new FreqHitsCategoryManager();

        if (!$this->checkFilename(Configuration::get('freqHitsCategoryFileRegex'))) {
            return $params;
        }

        $freqHitsRows = $this->importFromFile($freqHitsCategoryReader, $_FILES['filename']['tmp_name'], $_FILES['filename']['name']);
        if(!empty($freqHitsRows)){
            $this->importToDB(
                $freqHitsRows,
                $freqHitsCategoryManager,
                '"FREQ CATEGORY" data was imported successfully!',
                'freq_of_hits_per_cust_category'
            );
        }
        

        return $params;
    }


    public function freqHitsScenarioAction() {
        $params = [
            'viewName' => 'freq_hits_scenario.php',
        ];
        $freqHitsScenarioReader  = new FileReader(Configuration::get('freqHitsScenarioFieldsColumn'));
        $freqHitsScenarioManager = new FreqHitsScenarioManager();

        if (!$this->checkFilename(Configuration::get('freqHitsScenarioFileRegex'))) {
            return $params;
        }

        $freqHitsRows = $this->importFromFile($freqHitsScenarioReader, $_FILES['filename']['tmp_name'], $_FILES['filename']['name']);

        if(!empty($freqHitsRows)){
            $this->importToDB(
                $freqHitsRows,
                $freqHitsScenarioManager,
                '"FREQ INDICATOR (Scenario)" data was imported successfully!',
                'freq_of_hits_per_scenario'
            );
        }

        return $params;
    }

    public function seuilAction() {
        $params = [
            'viewName' => 'seuil.php',
        ];
        $seuilReader  = new SeuilReader(Configuration::get('seuilFieldsColumn'));
        $seuilManager = new SeuilManager();
        /** @TODO: Get rate from databse */
        $rate   = 10;

        if (!$this->checkFilename(Configuration::get('seuilFileRegex'))) {
            return $params;
        }

        $this->importToDB(
            $this->importFromFile($seuilReader, $_FILES['filename']['tmp_name'], $_FILES['filename']['name'], $rate),
            $seuilManager,
            '"Seuil" data was imported successfully!',
            'seuil'
        );

        return $params;
    }

    public function rateAction() {
        $params = [
            'viewName' => 'rate.php',
        ];
        $rateManager = new RateManager();

        if (!$this->checkFilename(Configuration::get('rateFileRegex'), 'Upload xls by browsing to file and clicking on Upload')) {
            return $params;
        }

        try {
            $rows = PHPExcel_IOFactory::load($_FILES['filename']['tmp_name'])->getSheet(0)->toArray();
            $titles = array_shift($rows);
            $titles = array_map('trim', $titles);
            $rateFieldsColumn = Configuration::get('rateFieldsColumn');

            $missingColumns = [];
            $isStructureValid = FileReader::isStructureValid(
                $titles,
                $rateFieldsColumn,
                $missingColumns
            );

            if (!$isStructureValid && count($missingColumns)) {
                throw new FileStructureException([$_FILES['filename']['name'], $missingColumns]);
            }

            $rateFieldsColumn = array_flip($rateFieldsColumn);
            $rows = array_map(
                function($row) use ($rateFieldsColumn) {
                    return array_combine($rateFieldsColumn, array_slice(array_map('trim', $row), 0, count($rateFieldsColumn)));
                },
                $rows
            );

            $this->importToDB(
                $rows,
                $rateManager,
                '"Conversion" data was imported successfully!',
                'taux',
                'update',
                false
            );
        } catch (FileStructureException $exception) {
            $this->session->addMessageFlash('FileStructureException'.$exception->getMessage(), 'error');
        }catch (DuplicateEntryException $exception) {
            $this->session->addMessageFlash($exception->getMessage(), 'error');
            $this->session->addMessageFlash('Import aborted', 'error');
        }

        return $params;
    }


    public function categoryAction() {
        $params = [
            'viewName' => 'category.php',
        ];
        $categoryReader  = new FileReader(Configuration::get('categoryFieldsColumn'));
        $categoryManager = new CategoryManager();

        if (!$this->checkFilename(Configuration::get('categoryFileRegex'))) {
            return $params;
        }

        $categoryRows = $this->importFromFile($categoryReader, $_FILES['filename']['tmp_name'], $_FILES['filename']['name']);

        if(!empty($categoryRows)){
            $this->importToDB(
                $categoryRows,
                $categoryManager,
                '"Category" data was imported successfully!',
                'category'
            );
        }

        return $params;
    }

    public function scenarioAction() {
        $params = [
            'viewName' => 'scenario.php',
        ];
        $scenarioReader  = new FileReader(Configuration::get('scenarioFieldsColumn'));
        $scenarioManager = new ScenarioManager();

        if (!$this->checkFilename(Configuration::get('scenarioFileRegex'))) {
            return $params;
        }

        $scenariosRows = $this->importFromFile($scenarioReader, $_FILES['filename']['tmp_name'], $_FILES['filename']['name']);

        if(!empty($scenariosRows)){
            $this->importToDB(
                $scenariosRows,
                $scenarioManager,
                '"scenario" data was imported successfully!',
                'senario',
                'insert',
                false
            );
        }

        return $params;
    }

    public function riskCountriesAction() {
        $params = [
            'viewName' => 'risk_countries.php',
        ];
        $CRReader  = new FileReader(Configuration::get('riskCountriesFieldsColumn'));
        $CRManager    = new RiskCountriesManager();

        if (!$this->checkFilename(Configuration::get('riskCountriesFileRegex'))) {
            return $params;
        }

        $countriesRows = $this->importFromFile($CRReader, $_FILES['filename']['tmp_name'], $_FILES['filename']['name']);


        if(!empty($countriesRows)){
            $this->importToDB(
                $countriesRows,
                $CRManager,
                '\'Risk Countries\' s data was imported successfully!',
                'Risk Countries',
                'insert',
                false
            );
        }

        return $params;
    }

    public function transactionsAction() {
        $params = [
            'viewName' => 'transactions.php',
        ];
        $transactionReader  = new FileReader(Configuration::get('transactionFieldsColumn'));
        $transactionManager = new TransactionManager();

        if (!$this->checkFilename(Configuration::get('transactionFileRegex'))) {
            return $params;
        }

        $countriesRows = $this->importFromFile($transactionReader, $_FILES['filename']['tmp_name'], $_FILES['filename']['name']);

        if(!empty($countriesRows)){
            $this->importToDB(
                $countriesRows,
                $transactionManager,
                '\'Transactions\' s data was imported successfully!',
                'Transactions',
                'insert',
                false,
                ['start_month', 'prev_month', 'bank_id']
            );
        }

        return $params;
    }


    public function deviseAction() {
    $params = [
        'viewName' => 'devise.php',
    ];
    $deviseManager = new DeviseManager();

    if (!$this->checkFilename(Configuration::get('deviseFileRegex'), 'Upload xls by browsing to file and clicking on Upload')) {
        return $params;
    }

    try {
        $rows = PHPExcel_IOFactory::load($_FILES['filename']['tmp_name'])->getSheet(0)->toArray();
        $titles = array_shift($rows);
        $titles = array_map('trim', $titles);

        $deviseFieldsColumn = Configuration::get('deviseFieldsColumn');

        $missingColumns = [];
        $isStructureValid = DeviseReader::isStructureValidUtf8(
            $titles,
            $deviseFieldsColumn,
            $missingColumns
        );

        if (!$isStructureValid && count($missingColumns)) {
            throw new FileStructureException([$_FILES['filename']['name'], $missingColumns]);
        }

        $deviseFieldsColumn = array_flip($deviseFieldsColumn);
        $rows = array_map(
            function($row) use ($deviseFieldsColumn) {
                return array_combine($deviseFieldsColumn, array_slice(array_map('trim', $row), 0, count($deviseFieldsColumn)));
            },
            $rows
        );

        $this->importToDB(
            $rows,
            $deviseManager,
            '"Devise" data was imported successfully!',
            'devise',
            'insert',
            false
        );
    } catch (FileStructureException $exception) {
        $this->session->addMessageFlash('FileStructureException'.$exception->getMessage(), 'error');
    }catch (DuplicateEntryException $exception) {
        $this->session->addMessageFlash($exception->getMessage(), 'error');
        $this->session->addMessageFlash('Import aborted', 'error');
    }

    return $params;
}

    protected function checkFilename($pattern, $messageWarning=null, $fileFormName='filename'){
        $filename = isset($_FILES[$fileFormName]['name']) ? $_FILES[$fileFormName]['name'] : false;
        if (isset($_POST['submit']) && false !== $filename && is_uploaded_file($_FILES[$fileFormName]['tmp_name'])) {
            if (!preg_match($pattern.'i', $filename)) {
                $this->session->addMessageFlash(
                    'The file name "'.$filename.'" dose not match, "...'.
                    str_replace(['$', '`', '\.'], ['', '', '.'], $pattern).'"',
                    'warning'
                );
            }

            return true;

        }

        $this->session->addMessageFlash($messageWarning ?: 'Upload new csv by browsing to file and clicking on Upload', 'warning');

        return false;
    }

    protected function importFromFile(FileReader $fileReader, $filePath, $filename, $params=false){
        try {
            return $fileReader->readRows($filePath, $filename, true, false, $params);
        } catch (FileStructureException $exception) {
            $this->isDev()
             ? $this->session->addMessageFlash($exception->getMessage(), 'error') 
             : $this->session->addMessageFlash(
                'File columns missing, check the imported file! &nbsp;&nbsp;"<small><b>'.$filename.'</b></small>"', 'error'
            );

            return false;
        }
    }

    protected function importToDB($rows, $dbtManager, $messageSuccess, $context,  $method ='insert', $ignoreIndex=true, $columnsConditions=[]){
        try {
            $errors = $resultRows = $ignoredRows = [];
            if($ignoreIndex){
                // $dbtManager->IgnoreIndex();
            }

            $deleted = false;
            if('update' == $method){
                $resultRows  = $dbtManager->updateData($rows, $errors, $deleted);
                $ignoredRows = array_filter(
                    $resultRows,
                    function($row){
                        return $row['error'];
                    }
                );
            }else{
                /** @var RiskCountriesManager $dbtManager */
                $resultRows = $dbtManager->insertData($rows, $errors, $deleted, $columnsConditions);
                $ignoredRows = array_filter(
                    $resultRows,
                    function($row){
                        return $row['error'];
                    }
                );
            }

            if ($inserted = count($resultRows) - count($ignoredRows)){
                $messageSuccess .= '<br>';
                if(false !== $deleted) {
                    $messageSuccess .= $deleted.' line'.($deleted>1?'s':'').' was deleted.<br>';
                }
                $messageSuccess .= ' <b>'.$inserted.'/'.count($resultRows).' line'.($deleted>1?'s':'').' was '.('update' == $method ? 'updated' : 'inserted').'</b>.';
                $this->session->addMessageFlash(
                    $messageSuccess,
                    'success'
                );
            }else{
                $this->session->addMessageFlash(
                    'No data was imported!',
                    'warning'
                );
            }

            $this->addModification($context);
            if (count($errors)) {
                foreach (array_unique($errors) as $error) {
                    $this->session->addMessageFlash($error, 'warning');
                }
            }
            if ($countIgnoredRows = count($ignoredRows)){
                $this->session->addMessageFlash('Some data was ignored! ('.$countIgnoredRows.'/'.count($resultRows).')', 'warning');
            }

            $this->redirect('Upload');
            
        } catch (DuplicateEntryException $exception) {
            $this->session->addMessageFlash($exception->getMessage(), 'error');
            $this->session->addMessageFlash('Line ignored', 'error');
        }
    }

    protected function addModification($table, $username=null){
        if(!$this->modificationManager){
            $this->modificationManager = new ModificationManager();
        }

        if(null === $username){
            $username = $this->getUsername();
        }

        $this->modificationManager->add($table, $username);
    }
}
