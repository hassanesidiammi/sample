<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class ConfigurationController extends BaseController {
    public function __construct($menu) {
        parent::__construct($menu);
        $this->grantAccess();
    }

    public function indexAction() {
        $loader = new MigrationLoader(new MigrationManager());
        $migrationManager = new MigrationManager();

        $executed = $loader->up();
        $migrations = $migrationManager->getLoaded('DESC');

        return [
            'viewName'   => 'index.php',
            'migrations' => $migrations,
            'migration'  => $executed,
        ];
    }

    public function mappingAction() {
        $manager      = new SubsidiaryManager();

        return [
            'viewName'     => 'mapping.php',
            'banks'        => $manager->getBanks(),
            'subsidiaries' => $manager->getAllWithBank(),
            'id'           => !empty($_GET['id']) ? $_GET['id'] : false,
        ];
    }

    public function mappingBankAction() {
        $manager = new BankManager();

        return [
            'viewName'  => 'mapping_bank.php',
            'banks'     => $manager->getAllWithZone(true),
            'zones'     => $manager->getZones(),
            'countries' => $manager->getCountries(),
            'id'        => !empty($_GET['id']) ? $_GET['id'] : false,
        ];
    }

    public function scenariosAction() {
        $manager = new ScenariosManager();

        return [
            'viewName'  => 'scenarios.php',
            'scenarios' => $manager->getAllWithFields(),
            'scores'    => array_combine(range(1, 100), range(1, 100)),
            'id'        => !empty($_GET['id']) ? $_GET['id'] : false,
        ];
    }

    public function usersAction() {
        $manager = new UserManager();
        if (!Configuration::is('disable_security_login') && !$manager->isUerAdmin($this->getUsername())) {
            $this->session->addMessageFlash('Not allowed!', 'warning');

            return $this->redirect('Configuration');
        }

        return [
            'viewName'  => 'users.php',
            'users'     => $manager->getAll(),
            'id'        => !empty($_GET['id']) ? $_GET['id'] : false,
        ];
    }

    public function scenarioVariablesAction() {
        $filterScenario = isset($_POST['scenario']) ? $_POST['scenario'] : false;
        $filterCategory = isset($_POST['category']) ? $_POST['category'] : false;
        $filterVariable = isset($_POST['variable']) ? $_POST['variable'] : false;
        $filterInternal = isset($_POST['internal']) ? $_POST['internal'] : false;
        $filterLike     = isset($_POST['like']) ? $_POST['like'] : false;

        $scenariosManager = new ScenarioVariablesManager();
        $manager          = new ScenarioVariablesManager();

        $scenarios     = $scenariosManager->getAll();
        $categoryNames = Configuration::get('seuilGlobalCategories');
        $variableNames = Configuration::get('scenarioVariables');
        asort($scenarios);

        if(empty($_GET['id']) || empty($_POST['scenarios'][$_GET['id']])) {
            // $this->session->addMessageFlash('Errors occurred!', 'warning');

            // return $this->redirect('Configuration', 'scenarios');
        }
        $scenarioVariables = [];
        if (!empty($_POST)) {
            $internal = !empty($filterInternal) && array_key_exists($filterInternal, $scenarios) ? $scenarios[$filterInternal] : false;
            $scenarioVariables = $manager->getAllVariables($filterScenario, $filterCategory, $filterVariable, $internal, $filterLike);
        }elseif (!empty($_GET['scenario']) && array_key_exists($_GET['scenario'], $scenarios)) {
            $filterScenario = isset($_GET['scenario']) ? $_GET['scenario'] : false;
            $filterCategory = isset($_GET['category']) ? $_GET['category'] : false;
            $filterVariable = isset($_GET['variable']) ? $_GET['variable'] : false;
            $filterInternal = isset($_GET['internal']) ? $_GET['internal'] : false;
            $filterLike     = isset($_GET['like'])     ? $_GET['like'] : false;
            $internal = !empty($filterInternal) && array_key_exists($filterInternal, $scenarios) ? $scenarios[$filterInternal] : false;
            $scenarioVariables = $manager->getAllVariables($filterScenario, $filterCategory, $filterVariable, $internal, $filterLike);
        }

        return [
            'viewName'   => 'scenario_variables.php',
            'variables'  => $scenarioVariables,
            'category_names' => $categoryNames,
            'id'         => !empty($_GET['id']) ? $_GET['id'] : false,

            'scenarios'        => $scenarios,
            'filter_scenario'  => $filterScenario,
            'all_categories'   => $categoryNames,
            'filter_category'  => $filterCategory,
            'all_variables'    => $variableNames,
            'filter_variable'  => $filterVariable,
            'filter_internal'  => $filterInternal,
            'filter_like'      => $filterLike,
            'allow_add'        => true,
        ];
    }

    public function updateSubsidiaryAction() {
        $manager      = new SubsidiaryManager();

        if(empty($_GET['id']) || empty($_POST['subsidiaries'][$_GET['id']])) {
            $this->session->addMessageFlash('Errors occurred!', 'warning');

            return $this->redirect('Configuration', 'mapping');
        }

        $id = $_GET['id'];
        $subsidiaryData = $_POST['subsidiaries'][$_GET['id']];

        $errors = [];
        $manager->updateWith($subsidiaryData, false, true, $errors);
        if (!empty($errors)) {
            $this->session->addMessageFlash(is_array($errors) ? array_shift($errors) : $errors, 'warning');
        } else {
            $this->session->addMessageFlash('Data was updated successfully.', 'success');
        }


        $this->redirect('Configuration', 'mapping', ['id' => $id], 'subsidiary'.$id);
    }

    public function updateBankAction() {
        $manager      = new BankManager();

        if(empty($_GET['id']) || empty($_POST['banks'][$_GET['id']])) {
            $this->session->addMessageFlash('Errors occurred!', 'warning');

            return $this->redirect('Configuration', 'mappingBank');
        }

        $id = $_GET['id'];
        $subsidiaryData = $_POST['banks'][$_GET['id']];

        $errors = [];
        try {
            $manager->updateWith($subsidiaryData, false, true, $errors);

            if (!empty($errors)) {
                $this->session->addMessageFlash(is_array($errors) ? array_shift($errors) : $errors, 'warning');
            } else {
                $this->session->addMessageFlash('Data was updated successfully.', 'success');
            }
        } catch(\Exception $exception) {
            if (empty($subsidiaryData['zone_id'])) {
                $this->session->addMessageFlash('"zone" Must Not be empty!', 'error');
            }
            if (empty($subsidiaryData['country_id'])) {
                $this->session->addMessageFlash('"country" Must Not be empty!', 'error');
            }

            if (!empty($subsidiaryData['zone_id']) && !empty($subsidiaryData['country_id'])) {
                $this->session->addMessageFlash('Errors occurred!', 'error');
                $this->session->addMessageFlash($exception->getMessage(), 'error');
            }
        }

        $this->redirect('Configuration', 'mappingBank', ['id' => $id], 'bank'.$id);
    }

    public function updateUserAction() {
        $manager = new UserManager();

        if (!Configuration::is('disable_security_login') && !$manager->isUerAdmin($this->getUsername())) {
            $this->session->addMessageFlash('Not allowed!', 'warning');

            return $this->redirect('Configuration');
        }

        if(empty($_GET['id']) || empty($_POST['users'][$_GET['id']])) {
            $this->session->addMessageFlash('Errors occurred!', 'warning');

            return $this->redirect('Configuration', 'users');
        }

        $id = $_GET['id'];
        $userData = $_POST['users'][$_GET['id']];

        $errors = [];
        $manager->updateWith($userData, false, true, $errors);
        if (!empty($errors)) {
            $this->session->addMessageFlash(is_array($errors) ? array_shift($errors) : $errors, 'warning');
        } else {
            $this->session->addMessageFlash('Data was updated successfully.', 'success');
        }

        $this->redirect('Configuration', 'users', ['id' => $id], 'users_'.$id);
    }

    public function updateScenarioAction() {
        $manager      = new ScenariosManager();

        if(empty($_GET['id']) || empty($_POST['scenarios'][$_GET['id']])) {
            $this->session->addMessageFlash('Errors occurred!', 'warning');

            return $this->redirect('Configuration', 'scenarios');
        }

        $id = $_GET['id'];
        $subsidiaryData = $_POST['scenarios'][$_GET['id']];

        if(array_key_exists('score', $subsidiaryData) && empty($subsidiaryData['score'])) {
            unset($subsidiaryData['score']);
        }
        if(array_key_exists('frequency', $subsidiaryData) && empty($subsidiaryData['frequency'])) {
            unset($subsidiaryData['frequency']);
        }

        $errors = [];
        $manager->updateWith($subsidiaryData, false, true, $errors);
        if (!empty($errors)) {
            $this->session->addMessageFlash(is_array($errors) ? array_shift($errors) : $errors, 'warning');
        } else {
            $this->session->addMessageFlash('Data was updated successfully.', 'success');
        }

        $this->redirect('Configuration', 'scenarios', ['id' => $id], 'scenarios'.$id);
    }

    public function updateScenarioVariableAction() {
        $scenariosManager = new ScenarioVariablesManager();
        $manager          = new ScenarioVariablesManager();

        $scenarios     = $scenariosManager->getAll();
        $categoryNames = Configuration::get('seuilGlobalCategories');
        $variableNames = Configuration::get('scenarioVariables');
        asort($scenarios);

        $id = $_GET['id'];
        $variableData = $_POST['variables'][$_GET['id']];

        if(array_key_exists('category_name', $variableData)) {
            // unset($variableData['category_name']);
        }
        if(array_key_exists('variable_name', $variableData)) {
            // unset($variableData['variable_name']);
        }

        $errors = [];

        try {
            $manager->updateWith($variableData, false, true, $errors);
        } catch (\Exception $exception) {
            if (23000 === $exception->getCode()) {
                $errors[] =
                    'Unicity Constraint violation! <br>'.
                    'A variable with the same data already exists!<br>'.
                    'Details:<br>'.
                    $exception->getMessage()
                ;
                if (false !== strpos($exception->getMessage(), 'IDX_uniq_scenario_variable_internal')) {
                    $unicityInternalViolation = true;
                } else {
                    $unicitViolation = true;
                }
            } else {
                $errors[] = $exception->getMessage();
            }
        }
        if (!empty($errors)) {
            $this->session->addMessageFlash(is_array($errors) ? array_shift($errors) : $errors, 'warning');
        } else {
            $this->session->addMessageFlash('Data was updated successfully.', 'success');
        }

        $this->redirect('Configuration', 'scenarioVariables', [
            'id'       => $variableData['id'],
            'scenario' => $variableData['scenario_id'],
            'like' => $variableData['external_current'],
        ], 'variables_'.$id);
    }

    public function newScenarioVariableAction() {
        $scenariosManager = new ScenarioVariablesManager();
        $manager          = new ScenarioVariablesManager();
        $scenarios        = $scenariosManager->getAll();
        $categoryNames    = Configuration::get('seuilGlobalCategories');
        $variableNames    = Configuration::get('scenarioVariables');
        asort($scenarios);

        $variableData = [];

        if(!empty($_POST) && !empty($_POST['variable'])) {
            $variableData = $_POST['variable'];
            $errors = [];

            $id = false;
            $unicitViolation = false;

            try {
                $id = $manager->insertWith($variableData, true, $errors);
            } catch (\Exception $exception) {
                if (23000 === $exception->getCode()) {
                    $errors[] =
                        'Unicity Constraint violation! <br>'.
                        'A variable with the same data already exists!<br>'.
                        'Details:<br>'.
                        $exception->getMessage()
                    ;
                    if (false !== strpos($exception->getMessage(), 'IDX_uniq_scenario_variable_internal')) {
                        $unicityInternalViolation = true;
                    } else {
                        $unicitViolation = true;
                    }
                } else {
                    $errors[] = $exception->getMessage();
                }
            }

            if (!empty($errors)) {
                $this->session->addMessageFlash(is_array($errors) ? array_shift($errors) : $errors, 'warning');
            } else {
                $this->session->addMessageFlash('Variable was added successfully.', 'success');
                $this->redirect('Configuration', 'scenarioVariables', [
                    'id'       => $id,
                    'scenario' => $variableData['scenario_id'],
                    'category' => $variableData['category_name'],
                ], 'variables_'.$id);
            }
        }

        return [
            'viewName'         => 'scenario_variable.php',
            'variable'         => $variableData,
            'scenarios'        => $scenarios,
            'category_names'   => $categoryNames,
            'variable_names'   => $variableNames,
            'unicit_violation' => !empty($unicitViolation),
            'unicit_internal_violation' => !empty($unicityInternalViolation),
        ];
    }

    public function newUserAction() {
        $manager          = new UserManager();

        if(empty($_POST) || empty($_POST['user'])) {
            $this->redirect('Configuration', 'users');
        }

        $userData = $_POST['user'];
        $userData['pass'] = UserManager::encrypt(UserManager::DEFAULT_PASS);
        $userData['code'] = '';
        $userData['nbr_connect'] = 0;
        $userData['nbr_chgMDP']  = 0;
        $userData['dates']       = date('Y-m-d');
        $userData['datechg']     = date('Y-m-d');
        $errors = [];

        $id = false;

        try {
            $id = $manager->insertWith($userData, true, $errors);
        } catch (\Exception $exception) {
            $errors[] = $exception->getMessage();
        }

        if (!empty($errors)) {
            $this->session->addMessageFlash(is_array($errors) ? array_shift($errors) : $errors, 'warning');
        } else {
            $this->session->addMessageFlash('Variable was added successfully.', 'success');
            $this->redirect('Configuration', 'users', [
                'id'       => $id,
                'user'     => $userData,
            ], 'users_'.$id);
        }

        $this->redirect('Configuration', 'users', [
            'id'       => $id,
            'user'     => !empty($userData['user']) ? $userData['user'] : false,
            'mail'     => !empty($userData['mail']) ? $userData['mail'] : false,
        ]);
    }

    protected function getScenarioVariable($id) {
        $manager = new ScenarioVariablesManager();

        if (empty($id)) {
            return false;
        }

        return $manager->get($id);
    }
}
