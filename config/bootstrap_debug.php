<?php

/**
 * Debug utility functions
 * Author: Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

 /** @TODO: make templates or html blocs */

const H_DEBUG_LEVEL = 5;
global $baseDir;

error_reporting(-1);

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    $message .= PHP_EOL . '' . $file . ':' . $line;
    throw new Exception($message, 0, null);
});

$baseDir = implode('/', array_slice(explode('/', str_replace('\\', '/', __DIR__)), 0, -2));

function getIndent($isTerminal, $lvl, $text='', $endLine=false) {
    if ($isTerminal) {
        return str_repeat('|    ', $lvl) . $text . ($endLine ? "|\n" : '');
    }
        
    return str_repeat('<span style="color:black">|</span>&nbsp;&nbsp;&nbsp;&nbsp;', $lvl) . $text . ($endLine ? '|<br>' : '');
}

function dump() {
    $args = func_get_args();
    _dumpHelper('dump_debug', $args);
}

function dr() {
    $args = func_get_args();
    _dumpHelper('print_r', $args);
}

function _dumpHelper($dumper, $data) {
    foreach ($data as $arg) {
        _dump($arg, $dumper);
    }
}

function _dump($data, $dumper = 'dump_debug') {
    static $i = 0;
    $i++;
    global $baseDir;

    $baseLength = strlen($baseDir) + 1;
    $traces = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5), 1);

    echo '<pre style="position: relative; margin: 10px auto; padding: 32px 10px 10px 10px; font-size: 14px; background: ' . ($i % 2 ? '#ffefef;' : '#ffffef;') . '")>';
    echo '<b style="position: absolute; top: 5px; right: 5px;">';
    print_trace(substr(array_key_exists('file', $traces[0]) ? $traces[0]['file'] : '?', $baseLength), array_key_exists('line', $traces[0]) ? $traces[0]['line'] : '?', 14);
    echo '</b>';
    if (is_string($data)) {
        echo '<div style="white-space: normal">';
    }
    call_user_func($dumper, $data);
    if (is_string($data)) {
        echo '</div>';
    }
    if (isset($traces[1], $traces[1]['function']) && !in_array($traces[1]['function'], ['require_once', 'require'])) {
        echo PHP_EOL . '<code style="font-size: 12px;">' . $traces[1]['function'] . '()</code>';
        if (isset($traces[2], $traces[2]['function']) && !in_array($traces[2]['function'], ['require_once', 'require'])) {
            echo '  <=  <code style="font-size: 12px;">' . $traces[2]['function'] . '()</code>';

            if (isset($traces[3], $traces[3]['function']) && !in_array($traces[3]['function'], ['require_once', 'require'])) {
                echo '  <=  <code style="font-size: 12px;">' . $traces[3]['function'] . '()</code>';
            }
        }
        echo PHP_EOL;
    }

    foreach ($traces as $trace) {
        if (!isset($trace['file'])) {
            continue;
        }
        print_trace(substr($trace['file'], $baseLength), $trace['line']);
    }
    echo '</pre>';
}

function print_trace($file, $line, $size = 12) {
    printf(
        '<code style="font-size: %dpx;">%s:%d</code>' . PHP_EOL,
        $size,
        $file,
        $line
    );
}

function dd() {
    $args = func_get_args();
    _dumpAndDieHelper($args);
}

function ddd() {
    $args = func_get_args();
    _dumpAndDieHelper($args, 'dump_debug');
}

function ddr() {
    $args = func_get_args();
    _dumpAndDieHelper($args, 'print_r');
}

function _dumpAndDieHelper($data, $dumper = 'dump_debug') {
    foreach ($data as $arg) {
        _dump($arg, $dumper);
    }
    die;
}

function dump_debug($input, $collapse = false) {
    $recursive = function ($data, $level = 0) use (&$recursive, $collapse) {
        global $argv;

        $isTerminal = isset($argv);

        if (!$isTerminal && $level == 0 && !defined("DUMP_DEBUG_SCRIPT")) {
            define("DUMP_DEBUG_SCRIPT", true);

            echo '<script language="Javascript">function toggleDisplay(id) {';
            echo 'var state = document.getElementById("container" + id).style.display;';
            echo 'document.getElementById("container" + id).style.display = state == "inline" ? "none" : "inline";';
            echo 'document.getElementById("plus" + id).style.display = state == "inline" ? "inline" : "none";';
            echo '}</script>' . "\n";
        }

        $type = !is_string($data) && is_callable($data) ? "Callable" : ucfirst(gettype($data));

        $typeData = null;
        $typeColor = null;
        $typeLength = null;

        switch ($type) {
            case 'String':
                $typeColor = "green";
                $typeLength = strlen($data);
                $stringEntities = htmlentities($data);
                if (empty($stringEntities) && $typeLength) {
                    $stringEntities = utf8_encode($data);
                    $stringEntities = htmlentities($stringEntities);
                }
                $typeData = "\"" . $stringEntities . "\"";
                break;

            case "Double":
            case "Float":
                $type = "Float";
                $typeColor = "#0099c5";
                $typeLength = strlen($data);
                $typeData = htmlentities($data);
                break;

            case "Integer":
                $typeColor = "red";
                $typeLength = strlen($data);
                $typeData = htmlentities($data);
                break;

            case "Boolean":
                $typeColor = "#92008d";
                $typeLength = strlen($data);
                $typeData = $data ? "TRUE" : "FALSE";
                break;

            case "NULL":
                $typeLength = 0;
                break;

            case "Array":
                $typeLength = count($data);
                break;
        }

        if ('Array' === $type) {
            $notEmpty = false;

            foreach($data as $key => $value) {
                if (!$notEmpty) {
                    $notEmpty = true;
                    if ($isTerminal) {
                        echo $type . ($typeLength !== null ? "(" . $typeLength . ")" : "")."\n";
                    } else {
                        $id = substr(md5(rand().":".$key.":".$level), 0, 8);
                        echo '<a href="javascript:toggleDisplay(\''. $id .'\');" style="text-decoration:none">'.
                             '<span style="color:#666611">' . $type . ($typeLength !== null ? '(' . $typeLength . ')' : '') . '</span>'.
                             '</a>'.
                             '<span id="plus'. $id .'" style="display: ' . ($collapse ? 'inline' : 'none') . ';">&nbsp;&#10549;</span>'.
                             '<div id="container'. $id .'" style="display: '. ($collapse ? '' : 'inline') . ';">';
                        echo '<br>';
                    }

                    echo getIndent($isTerminal, $level);
                }

                echo getIndent($isTerminal, $level + 1, '[' . $key . '] => ');

                if($level > H_DEBUG_LEVEL){
                    echo '...<br>';
                } else {
                    call_user_func($recursive, $value, $level+1);
                }
            }

            if ($notEmpty) {
                for ($i = 0; $i <= $level; $i++) {
                    echo $isTerminal ? "|    " : "<span style='color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;";
                }

                if (!$isTerminal) {
                    echo "</div>";
                }
            } else {
                echo $isTerminal ?
                    $type . ($typeLength !== null ? "(" . $typeLength . ")" : "") . "  " :
                    "<span style='color:#666622'>" . $type . ($typeLength !== null ? "(" . $typeLength . ")" : "") . "</span>&nbsp;&nbsp;";
            }

        } elseif ('Object' == $type) {
            $notEmpty = false;

            $reflection = new ReflectionObject($data);
            $properties = $reflection->getProperties();
            $methods    = $reflection->getMethods();
            $protected = $private = false;

            foreach($properties as $property) {
                $key = $property->getName();
                try {
                    if($property->isPrivate()){
                        $private = true;
                    }elseif($property->isProtected()){
                        $protected = true;
                    }
                    $value = $property->setAccessible(true);
                    $value = $property->getValue($data);
                } catch (Exception $e) {
                    die($e->getMessage());
                }

                if (!$notEmpty) {
                    $notEmpty = true;
                    if ($isTerminal) {
                        echo $type . ($typeLength !== null ? "(" . $typeLength . ")" : "")."\n";
                        echo getIndent($isTerminal, $level, '', true);
                    } else {
                        $id = substr(md5(rand().":".$key.":".$level), 0, 8);
                        echo '<a href="javascript:toggleDisplay(\''. $id .'\');" style="text-decoration:none">'.
                            '<span style="color:#666611">' . $type ."({$property->class})" . ($typeLength !== null ? '(' . $typeLength . ')' : '') . '</span>'.
                            '</a>'.
                            '<span id="plus'. $id .'" style="display: ' . ($collapse ? 'inline' : 'none') . ';">&nbsp;&#10549;</span>'.
                            '<div id="container'. $id .'" style="display: '. ($collapse ? '' : 'inline') . ';">';
                        echo '<br>';
                        echo getIndent($isTerminal, $level+1, '', true);
                    }
                }

                echo getIndent($isTerminal, $level + 2, $property->isPrivate() ? '- ' : ($property->isProtected() ? '~ ' : '+ ') . $key . ': ');

                if($level > H_DEBUG_LEVEL){
                    echo '...<br>';
                } else {
                    call_user_func($recursive, $value, $level+1);
                }
            }

            $protected = $private = false;

            if (!empty($methods)) {
                $key = isset($key) ? $key : 'OBJECTMETHODS';
                $id = substr(md5(rand().":".$key.":".$level), 0, 8);

                echo getIndent($isTerminal, $level + 1, '', true);
                echo getIndent($isTerminal, $level + 1, '<a href="javascript:toggleDisplay(\''. $id .'\');" style="text-decoration:none">'.
                    '<span style="color:#000000">|</span><span style="color:#666611">--Methods--</span>'.
                    '</a>'.
                    '<span id="plus'. $id .'" style="display: inline;">&nbsp;&#10549;</span>'.
                    '<div id="container'. $id .'" style="display: none;">');
                foreach($methods as $method) {
                    $name = $method->getName();
                    $params = $method->getParameters();
                    
                    echo '<br>';
                    echo getIndent($isTerminal, $level+2, ($method->isPrivate() ? '- ' : ($method->isProtected() ? '~ ' : '+ ')) . $name);
                    if (!empty($params)) {
                        echo '(';
                        $params = array_map(function($param){
                            return $param->getName();
                        }, $params);

                        echo implode($params);
                        echo ')';
                    }
                }

                if (!$isTerminal) {
                    echo "</div>";
                    echo '<br>';
                }
            }
            
            echo getIndent($isTerminal, $level + 1);

            if (!$isTerminal) {
                echo "</div>";
            }

        } else {
            echo $isTerminal ?
                $type . ($typeLength !== null ? "(" . $typeLength . ")" : "") . "  " :
                "<span style='color:#666633'>" . $type . ($typeLength !== null ? "(" . $typeLength . ")" : "") . "</span>&nbsp;&nbsp;";
            if ($typeData != null) {
                echo $isTerminal ? $typeData : "<span style='color:" . $typeColor . "'> ". $typeData ."</span>";
            }
        }

        echo $isTerminal ? "\n" : "<br />";
    };

    call_user_func($recursive, $input);
}
