<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */


const H_DEBUG_LEVEL = 4;
global $baseDire;

error_reporting(-1);

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    $message .= PHP_EOL.''.$file.':'.$line;
    throw new Exception($message, 0, null);
});

$baseDire = explode('/',str_replace('\\', '/', __DIR__));
array_pop($baseDire);
array_pop($baseDire);
$baseDire = implode('/', $baseDire);

function dump($data) {
    if(func_num_args() > 1){
        foreach (func_get_args() as $arg) {
            _dump($arg, 'dump_debug');
        }

        return;
    }

    _dump($data, 'dump_debug');
}
function dr($data) {
    if(func_num_args() > 1){
        foreach (func_get_args() as $arg) {
            _dump($arg, 'print_r');
        }

        return;
    }

    _dump($data, 'print_r');
}

function _dump($data, $dumper='dump_debug') {
    static $i = 0;
    $i++;
    global $baseDire;

    $baseLenth = strlen($baseDire) + 1;
    $traces    = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5), 1);

    echo '<pre style="position: relative; margin: 10px auto; padding: 32px 10px 10px 10px; font-size: 14px; background: '.($i % 2?'#ffefef;':'#ffffef;').'")>';
    echo '<b style="position: absolute; top: 5px; right: 5px;">';
    print_trace(substr($traces[0]['file'], $baseLenth), $traces[0]['line'], 14);
    echo '</b>';
    if (is_string($data)) echo '<div style="white-space: normal">';
    call_user_func($dumper, $data);
    if (is_string($data)) echo '</div>';
    if (isset($traces[1], $traces[1]['function']) && 'require_once' !== $traces[1]['function']&& 'require' !== $traces[1]['function']){
        echo PHP_EOL.'<code style="font-size: 12px;">'.$traces[1]['function'].'()</code>';
        if (isset($traces[2], $traces[2]['function']) && 'require_once' !== $traces[2]['function']&& 'require' !== $traces[2]['function']){
            echo '  <=  <code style="font-size: 12px;">'.$traces[2]['function'].'()</code>';

            if (isset($traces[3], $traces[3]['function']) && 'require_once' !== $traces[3]['function']&& 'require' !== $traces[3]['function']){
                echo '  <=  <code style="font-size: 12px;">'.$traces[3]['function'].'()</code>';
            }
        }
        echo PHP_EOL;
    }

    foreach ($traces as $trace){
        if (!isset($trace['file'])) {
            continue;
        }
        print_trace(substr($trace['file'], $baseLenth), $trace['line']);
    }
    echo '</pre>';
}

function print_trace($file, $line, $siz=12){
    printf(
        '<code style="font-size: %dpx;">%s:%d</code>'.PHP_EOL,
        $siz,
        $file,
        $line
    );
}

function dd($data) {
    if(func_get_args()>1){
        foreach (func_get_args() as $arg) {
            _dump($arg);
        }

        die;
    }

    _dump($data);
    die;
}


function ddd($data) {
    if(func_get_args()>1){
        foreach (func_get_args() as $arg) {
            _dump($arg, 'dump_debug');
        }

        die;
    }

    _dump($data);
    die;
}

function ddr($data) {
    if(func_get_args()>1){
        foreach (func_get_args() as $arg) {
            _dump($arg, 'print_r');
        }

        die;
    }

    _dump($data, 'print_r');
    die;
}

function dump_debug($input, $collapse=false) {
    $recursive = function($data, $level=0) use (&$recursive, $collapse) {
        global $argv;

        $isTerminal = isset($argv);

        if (!$isTerminal && $level == 0 && !defined("DUMP_DEBUG_SCRIPT")) {
            define("DUMP_DEBUG_SCRIPT", true);

            echo '<script language="Javascript">function toggleDisplay(id) {';
            echo 'var state = document.getElementById("container"+id).style.display;';
            echo 'document.getElementById("container"+id).style.display = state == "inline" ? "none" : "inline";';
            echo 'document.getElementById("plus"+id).style.display = state == "inline" ? "inline" : "none";';
            echo '}</script>'."\n";
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
                $typeData = "\"" . $stringEntities . "\""; break;

            case "Double":
            case "Float":
                $type = "Float";
                $typeColor = "#0099c5";
                $typeLength = strlen($data);
                $typeData = htmlentities($data); break;

            case "Integer":
                $typeColor = "red";
                $typeLength = strlen($data);
                $typeData = htmlentities($data); break;

            case "Boolean":
                $typeColor = "#92008d";
                $typeLength = strlen($data);
                $typeData = $data ? "TRUE" : "FALSE"; break;

            case "NULL":
                $typeLength = 0; break;

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

                    echo str_repeat($isTerminal ? '|    ' : '<span style="color:black">|</span>&nbsp;&nbsp;&nbsp;&nbsp;',$level+1);
                    echo $isTerminal ? '\n' : '<br>';
                }

                echo str_repeat($isTerminal ? '|    ' : '<span style="color:black">|</span>&nbsp;&nbsp;&nbsp;&nbsp;',$level+1);
                echo $isTerminal ? '[' . $key . '] => ' : '<span style="color:black">['. $key .']&nbsp;=>&nbsp;</span>';


                if($level > H_DEBUG_LEVEL){
                    echo '...<br>';
                } else {
                    call_user_func($recursive, $value, $level+1);
                }
            }
            if ($notEmpty) {
                for ($i=0; $i <= $level; $i++) {
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
            $protected = $private = false;

            foreach($properties as $property) {
                /** @var ReflectionProperty $property */
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
                        echo str_repeat('|    ',$level+1);
                        echo '\n';
                    } else {
                        $id = substr(md5(rand().":".$key.":".$level), 0, 8);
                        echo '<a href="javascript:toggleDisplay(\''. $id .'\');" style="text-decoration:none">'.
                            '<span style="color:#666611">' . $type ."({$property->class})" . ($typeLength !== null ? '(' . $typeLength . ')' : '') . '</span>'.
                            '</a>'.
                            '<span id="plus'. $id .'" style="display: ' . ($collapse ? 'inline' : 'none') . ';">&nbsp;&#10549;</span>'.
                            '<div id="container'. $id .'" style="display: '. ($collapse ? '' : 'inline') . ';">';
                        echo '<br>';
                        echo str_repeat('<span style="color:black">|</span>&nbsp;&nbsp;&nbsp;&nbsp;',$level+1);
                        echo '<br>';
                    }


                }

                echo str_repeat($isTerminal ? '|    ' : '<span style="color:black">|</span>&nbsp;&nbsp;&nbsp;&nbsp;',$level+1);
                $visibility = $private ? '- ' : ($protected ? '~ ' : '+ ');
                echo $isTerminal ? $visibility.$key . ': ' : '<span style="color:black">'.$visibility.$key .': </span>';


                if($level > H_DEBUG_LEVEL){
                    echo '...<br>';
                } else {
                    call_user_func($recursive, $value, $level+1);
                }
            }

            if ($notEmpty) {
                for ($i=0; $i <= $level; $i++) {
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

