<?php

/**
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class SimpleAutoloader
{
    private $configuration;
    private $controllers;
    private $dbClasses;
    private $fsClasses;
    private $exceptionClasses;
    private $viewsClasses;
    private $utilities;
    private static $loader = false;

    private function __construct()
    {
        $this->controllers = [
            'BaseController' => 'src/BaseController.php',
            'UploadController' => 'src/Controllers/UploadController.php',
            'AlertPerStatusController' => 'src/Controllers/AlertPerStatusController.php',
            'HitPerScoreController' => 'src/Controllers/HitPerScoreController.php',
            'CategoryController' => 'src/Controllers/CategoryController.php',
            'SARController' => 'src/Controllers/SARController.php',
            'RiskController' => 'src/Controllers/RiskController.php',
            'TransactionController' => 'src/Controllers/TransactionController.php',
            'ScenarioController' => 'src/Controllers/ScenarioController.php',
            'AlertController' => 'src/Controllers/AlertController.php',
            'ThresholdsperCController' => 'src/Controllers/ThresholdsperCController.php',
            'ThresholdsperCaIController' => 'src/Controllers/ThresholdsperCaIController.php',
            'UpdateController' => 'src/Controllers/UpdateController.php',

            'ConfigurationController' => 'src/Controllers/Admin/ConfigurationController.php',
        ];
        $this->viewsClasses = [
            'Views' => 'src/Views.php',
        ];

        $this->dbClasses = [
            'MysqliMapper' => 'Lib/DB/Mappers/MysqliMapper.php',
            'MysqliWithoutParamsMapper' => 'Lib/DB/Mappers/MysqliWithoutParamsMapper.php',
            'PDOMapper' => 'Lib/DB/Mappers/PDOMapper.php',
            'DBManager' => 'Lib/DB/Managers/DBManager.php',
            'AlertManager' => 'Lib/DB/Managers/AlertManager.php',
            'FreqHitsCategoryManager' => 'Lib/DB/Managers/FreqHitsCategoryManager.php',
            'FreqHitsScenarioManager' => 'Lib/DB/Managers/FreqHitsScenarioManager.php',
            'SeuilManager' => 'Lib/DB/Managers/SeuilManager.php',
            'RateManager'  => 'Lib/DB/Managers/RateManager.php',
            'DeviseManager'  => 'Lib/DB/Managers/DeviseManager.php',
            'CategoryManager' => 'Lib/DB/Managers/CategoryManager.php',
            'ScenarioManager' => 'Lib/DB/Managers/ScenarioManager.php',
            'ScenariosManager' => 'Lib/DB/Managers/ScenariosManager.php',
            'ScenarioVariablesManager' => 'Lib/DB/Managers/ScenarioVariablesManager.php',
            'StatusManager' => 'Lib/DB/Managers/StatusManager.php',
            'RiskCountriesManager' => 'Lib/DB/Managers/RiskCountriesManager.php',
            'UserManager' => 'Lib/DB/Managers/UserManager.php',
            'TransactionManager'  => 'Lib/DB/Managers/TransactionManager.php',
            'ModificationManager' => 'Lib/DB/Managers/ModificationManager.php',
            'MigrationManager' => 'Lib/DB/Managers/MigrationManager.php',
            'FilterManager' => 'Lib/DB/Managers/FilterManager.php',

            'BankManager' => 'Lib/DB/Managers/BankManager.php',
            'SubsidiaryManager' => 'Lib/DB/Managers/SubsidiaryManager.php',
        ];

        $this->fsClasses = [
            'FileReader' => 'Lib/Filesystem/FileReader.php',
            'FileWriter' => 'Lib/Filesystem/FileWriter.php',
            'SeuilReader' => 'Lib/Filesystem/SeuilReader.php',
            'AlertReader' => 'Lib/Filesystem/AlertReader.php',
            'DeviseReader' => 'Lib/Filesystem/DeviseReader.php',
            'PHPExcel_IOFactory' => 'Lib/Depreciated/Classes/PHPExcel/IOFactory.php',
        ];

        $this->exceptionClasses = [
            'FileNotFoundException' => 'Lib/Filesystem/Exception/FileNotFoundException.php',
            'VariableNotFoundException' => 'Lib/Filesystem/Exception/VariableNotFoundException.php',
            'FileStructureException' => 'Lib/Filesystem/Exception/FileStructureException.php',
            'ExceptionMessageException' => 'Lib/Filesystem/Exception/ExceptionMessageException.php',

            'DuplicateEntryException' => 'Lib/DB/Managers/Exception/DuplicateEntryException.php',
            'TableNotFoundException' => 'Lib/DB/Managers/Exception/TableNotFoundException.php',

            'SecurityException' => 'src/Exception/SecurityException.php',
        ];

        $this->configuration = [
            'Configuration' => 'src/Configuration.php',
        ];

        $this->utilities = [
            'UrlGeneratorTrait' => 'src/UrlGeneratorTrait.php',
            'Menu' => 'src/Menu.php',
            'BackMenu' => 'src/BackMenu.php',
            'Session' => 'src/Session.php',

            'MigrationLoader' => 'src/Migrations/MigrationLoader.php',
            'DateUtilities'   => 'Lib/Utils/DateUtilities.php',
        ];
    }

    public static function Register() {
        if (false === self::$loader) {
            self::$loader = new self;
        }

        spl_autoload_register([self::$loader, 'Load']);
    }

    public function Load($className){
        if(array_key_exists($className, $this->controllers)) {
            return require_once $this->controllers[$className];
        }
        if(array_key_exists($className, $this->viewsClasses)) {
            return require_once $this->viewsClasses[$className];
        }

        if(array_key_exists($className, $this->dbClasses)) {
            return require_once $this->dbClasses[$className];
        }

        if(array_key_exists($className, $this->fsClasses)) {
            return require_once $this->fsClasses[$className];
        }

        if(array_key_exists($className, $this->exceptionClasses)) {
            return require_once $this->exceptionClasses[$className];
        }

        if(array_key_exists($className, $this->configuration)) {
            return require_once $this->configuration[$className];
        }

        if(array_key_exists($className, $this->utilities)) {
            return require_once $this->utilities[$className];
        }

        return false;
    }
}
