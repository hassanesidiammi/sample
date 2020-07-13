<?php

/**
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class FileReader {
    private $columnFieldMapper;
    private $fieldColumnTitles;

    public function __construct($columnFieldMapper, $fieldColumnTitles=null) {
        $this->columnFieldMapper = $columnFieldMapper;

        $this->fieldColumnTitles = $fieldColumnTitles ?: array_flip($this->columnFieldMapper);
    }

    public function readRows($filePath, $filename='', $toDBFields=true, $callback=false, $params=false) {
        if (0 < (int) ini_get('max_execution_time') && (int) Configuration::get('max_upload_time') > (int) ini_get('max_execution_time')) {
            set_time_limit(Configuration::get('max_upload_time'));
        }
        $handle = fopen($filePath, "r");
        $titles = fgetcsv($handle, 8000, ";");
        $titles = array_map(function ($title){
            return trim(str_replace(['"', '\'', '?'], '', utf8_decode($title)));
        }, $titles);

        $missingColumns = [];
        self::isStructureValid($titles, $this->columnFieldMapper, $missingColumns);
        if (count($missingColumns)) {
            throw new FileStructureException([$filename, $missingColumns]);
        }

        $rows = [];
        while (($data = fgetcsv($handle, 8000, ";")) !== FALSE) {
            if (count($data) >= count($titles)){
                $row = array_combine($titles, array_slice($data, 0, count($titles)));
                if ($toDBFields) {
                    $rowFields = [];
                    foreach ($row as $key => $value) {
                        $field = self::getDBField($key, $this->fieldColumnTitles);

                        if ($toDBFields && !in_array($field, $this->fieldColumnTitles)) {
                            continue;
                        }
                        $rowFields[$field] = trim($value);
                    }
                    $row = $rowFields;
                }

                if ($callback) {
                    $row = call_user_func($callback, $row, $params);
                }

                $rows[] = $row;
            }
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @param $title
     *
     * @return string
     */
    public static function getDBField($title, $fieldColumnTitles) {

        if (array_key_exists($title, $fieldColumnTitles)) {
            return $fieldColumnTitles[$title];
        }

        if (false !== strpos($title, 'PERIOD')) {
            return $fieldColumnTitles['PERIOD'];
        }

        return $title;
    }

    public static function isStructureValid($columns, &$needleColumns, &$missingColumns, $ignore=false) {
        $missingColumns = array_diff($needleColumns, $columns);

        if ($ignore) {
            $missingColumns = array_filter(
                $missingColumns,
                function ($name) use ($ignore) {
                    return false === strpos($name, $ignore);
                }
            );
        }

        if (0 !== count($missingColumns)) {
            return false;
        }

        $rows = [];
        foreach ($columns as $value) {
            $index = array_search($value, $needleColumns);
            if (false !== $index) {
                $rows[$index] = $value;
            }
        }
        $needleColumns = $rows;

        return true;
    }

    public static function columnExists($columns, $search, &$missingColumns) {
        foreach ($columns as $column) {
            if(false !== strpos($column, $search)){
                return true;
            }
        }
        $missingColumns[] = $search;

        return false;
    }
}