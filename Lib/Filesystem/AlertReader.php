<?php

/**
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class AlertReader extends FileReader {

    public function readRows($filePath, $filename='', $toDBFields=true, $callback=false, $params=false) {
        return parent::readRows($filePath, $filename, $toDBFields, [$this, 'mapScenarios'], $params);
    }

    public function mapScenarios($data, ScenariosManager $scenariosManager, $idAlert=false) {
        $numberScenarios = array_key_exists('no_of_scenarios', $data) ? $data['no_of_scenarios'] : 50;
        $scenariosManager->loadScenarios();

        foreach (range(1, 50) as $currentNumber) {
            $item = 'ass_scenario_'.$currentNumber;
            $reference = $data[$item];
            // unset($data[$item]);
            $data[$item] = '';

            if (empty($reference) || $numberScenarios < $currentNumber){
                continue;
            }

            $row = [];
            $scenarioId = $scenariosManager->getScenarioId($reference, $item);

            if(empty($scenarioId) && !empty($reference)) {
                $scenario    = $scenariosManager->matchScenario($reference);
                $description = substr($reference, strpos($reference, $scenario) + strlen($scenario));
                $description = explode('/', $description);
                $row['description_en'] = trim($description[0]);
                $row['description_fr'] = trim(!empty($description[1]) ? $description[1] : $description[0]);

                $scenarioId = $scenariosManager->guessScenarioId(
                    $scenario,
                    !empty($row['description_en']) ? $row['description_en'] : '',
                    !empty($row['description_fr']) ? $row['description_fr'] : '',
                    null,
                    $reference
                );
            }

            if ($scenarioId) {
                $scenario = ['number' => $currentNumber, 'id' => $scenarioId, 'reference' => $reference];
                if (!empty($idAlert)){
                    $scenario['id_alert'] = $idAlert;
                }
                $data['scenario_ids'][] = $scenario;
            }
        }

        return $data;
    }
}