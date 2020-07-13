<?php

/**
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class SeuilReader extends FileReader {

    public function readRows($filePath, $filename='', $toDBFields=true, $callback=false, $params=false) {
        return parent::readRows($filePath, $filename, $toDBFields, [$this, 'setSeuilEur'], $params);
    }

    public function setSeuilEur($data, $rate) {
        $seuilEur = substr(
            $data['VAR_SYNTAX'],
            strrpos($data['VAR_SYNTAX'], '=') + 1,
            strlen($data['VAR_SYNTAX']) - strrpos($data['VAR_SYNTAX'], '=') + 1
        );

        if (false != strrpos($seuilEur, "'")) {
            $seuilEur = addslashes($seuilEur);
        } else {
            $seuilEur = floatval($seuilEur);
            $seuilEur = floatval($rate) == 0 ? $seuilEur . '/0' : $seuilEur / floatval($rate);
        }

        $data['VAR_SYNTAX_EUR'] = $seuilEur;
        $data['VAR_SYNTAX']     = strlen($data['VAR_SYNTAX']) > 300 ? substr($data['VAR_SYNTAX'], 0, 295).'...' :  $data['VAR_SYNTAX'];
        $data['VAR_SYNTAX_EUR'] = strlen($data['VAR_SYNTAX_EUR']) > 300 ? substr($data['VAR_SYNTAX_EUR'], 0, 295).'...' :  $data['VAR_SYNTAX_EUR'];
        $data['VAR_COMMENT'] = strlen($data['VAR_COMMENT']) > 150 ? substr($data['VAR_COMMENT'], 0, 145).'...' :  $data['VAR_COMMENT'];

        return $data;
    }
}