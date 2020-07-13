<?php

/**
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class DeviseReader extends FileReader {
    public static function isStructureValidUtf8(&$columns, &$needleColumns, &$missingColumns, $ignore=false) {
        $columns = array_map(function ($item) {
            $item = utf8_decode($item);
            return $item;
        }, $columns);

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
}