<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class AlertController extends BaseController {
    public function __construct($menu) {
        parent::__construct($menu);
        $this->grantAccess();
    }

    public function indexAction() {
        $alertManager = new AlertManager();
        $scenarioManager = new ScenarioManager();
        $selectedSubs    = $selectedPeriods = $scenariosJ =  $scenariosM =  $details = [];

        $filterBu   = array_key_exists('bu', $_POST) ? $_POST['bu'] : false;
        $filterZone = array_key_exists('zone', $_POST) ? $_POST['zone'] : false;
        $filterBank = array_key_exists('bank', $_POST) ? $_POST['bank'] : false;
        $filterStart = array_key_exists('startdate', $_POST) ? $_POST['startdate'] : false;
        $filterEnd   = array_key_exists('enddate', $_POST) ? $_POST['enddate'] : false;

        $bus  = $alertManager->getBUs();
        if($filterBu){
            $selectedBUs = [$filterBu => $bus[$filterBu]];
        }else{
            $selectedBUs = $bus;
        }
        $zones  = $alertManager->getZones(array_keys($selectedBUs));
        if($filterZone){
            $selectedZones = [$filterZone => $zones[$filterZone]];
        }else{
            $selectedZones = $zones;
        }

        $banks = $alertManager->getBanks(array_keys($selectedZones));
        $selectedBanks = [];
        if($filterBank && array_key_exists($filterBank, $banks)){
            $selectedBanks = [$filterBank => $banks[$filterBank]];
        }else{
            $selectedBanks = $banks;
        }

        $selectedSubs = $alertManager->getSubsidiaries(false, array_keys($selectedBanks));

        $starts = $alertManager->getPeriods(array_keys($selectedSubs));
        $ends   = $alertManager->getEnds(array_keys($selectedSubs), $filterStart);

        $selectedPeriods  = $this->getPeriodsRange($filterStart, $filterEnd);

        if (empty($selectedSubs)){
            $this->session->addMessageFlash('No subsidiary found for this selection!', 'warning');

            return [
                'all_bus'           => $bus,
                'all_zones'         => $zones,
                'all_banks'         => $banks,
                'all_starts'        => $starts,
                'all_ends'          => $ends,
                'subsidiaries'      => $selectedSubs,
                'periods'           => $selectedPeriods,
                'filter_bu'         => $filterBu,
                'filter_zone'       => $filterZone,
                'filter_bank'       => $filterBank,
                'filter_start'      => $filterStart,
                'filter_end'        => $filterEnd,

                'details'  => $details,
            ];
        }

        if ((!empty($_POST['submit'])  || !empty($_POST['export']))){
            $scenariosJ = $scenarioManager->getAll('J');
            $scenariosM = $scenarioManager->getAll('M');
            // $scenariosM = $scenarioManager->getAll(false, false, 'J');

            $rowsJ = $alertManager->getTreatmentDelay(array_keys($scenariosJ), array_keys($selectedSubs), $selectedPeriods, AlertManager::$statusTreated);
            $rowsM = $alertManager->getTreatmentDelay(array_keys($scenariosM), array_keys($selectedSubs), $selectedPeriods, AlertManager::$statusTreated);

            $details = [];

            foreach ($selectedSubs as $subsidiary) {
                foreach (array_reverse($selectedPeriods) as $period) {
                    $details[$subsidiary][$period]['J'] = array_key_exists($period, $rowsJ) && array_key_exists($subsidiary, $rowsJ[$period]) ? number_format($rowsJ[$period][$subsidiary], 2) : '-';
                    $details[$subsidiary][$period]['M'] = array_key_exists($period, $rowsM) && array_key_exists($subsidiary, $rowsM[$period]) ? number_format($rowsM[$period][$subsidiary], 2) : '-';
                }
            }
        }

        if (!empty($_POST['export'])){
            $fileWriter = new FileWriter();

            $periods = $details;
            $periods = array_shift($periods);

            $titles = array_map(function ($period){
                return ['value' => $period, 'style' => 'title', 'mergeNext' => 1];
            }, array_keys($periods));
            array_unshift($titles, ['value' => '', 'style' => 'title', 'center' => true,]);
            $rows = [$titles];

            $titles = [['value' => '', 'style' => 'title']];
            foreach ($periods as $period) {
                $titles[] = ['value' => 'J', 'style' => 'title'];
                $titles[] = ['value' => 'M', 'style' => 'title'];
            }
            $rows[] = $titles;

            foreach ($details as $subsidiary => $detail) {
                $row = [['value' => $subsidiary, 'style' => 'highlighted']];
                foreach ($detail as $period => $delay) {
                    $row[] = ['value' => $delay['J']];
                    $row[] = ['value' => $delay['M']];
                }
                $rows[] = $row;
            }

            $subsidiaries = array_shift($selectedSubs);
            if (count($selectedSubs)) {
                $subsidiaries .= '...';
            }

            $period = $selectedPeriods[0].'-'.$selectedPeriods[count($selectedPeriods) - 1];
            $fileWriter->write(
                $rows,
                'TreatmentDelay_'.$subsidiaries.'_'.$period,
                'Treatment Delay, '.$subsidiaries.' '.$period
            );
            die;
        }

        return [
            'all_bus'           => $bus,
            'all_zones'         => $zones,
            'all_banks'         => $banks,
            'all_starts'        => $starts,
            'all_ends'          => $ends,
            'subsidiaries'      => $selectedSubs,
            'periods'           => $selectedPeriods,
            'filter_bu'         => $filterBu,
            'filter_zone'       => $filterZone,
            'filter_bank'       => $filterBank,
            'filter_start'      => $filterStart,
            'filter_end'        => $filterEnd,

            'details'  => $details,
        ];
    }
}
