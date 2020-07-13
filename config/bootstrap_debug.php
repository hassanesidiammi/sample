<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

error_reporting(-1);

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    $message .= PHP_EOL.''.$file.':'.$line;
    throw new Exception($message, 0, null);
});

global $baseDire;
$baseDire = explode('/',str_replace('\\', '/', __DIR__));
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

function _dump($data, $dumper='var_dump') {
    static $i = 0;
    $i++;
    global $baseDire;

    $baseLenth = strlen($baseDire) + 1;
    $traces    = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5), 1);

    echo '<pre style="position: relative; margin: 10px auto; padding: 32px 10px 10px 10px; font-size: 14px; background: '.($i % 2?'#ffefef;':'#ffffef;').'")>';
    echo '<b style="position: absolute; top: 5px; right: 5px;">';
    print_trace(substr($traces[0]['file'], $baseLenth), $traces[0]['line'], 18);
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
        if ('Object' == $type) {
            try {
                $type .=' `'.get_class($data).'` ';
            } catch (\Exception $exception){

            }
        }else {
        }
        $type_data = null;
        $type_color = null;
        $type_length = null;

        switch ($type) {
            case "String":
                $type_color = "green";
                $type_length = strlen($data);
                $stringEntities = htmlentities($data);
                if (empty($stringEntities) && $type_length) {
                    $stringEntities = utf8_encode($data);
                    $stringEntities = htmlentities($stringEntities);
                }
                $type_data = "\"" . $stringEntities . "\""; break;

            case "Double": 
            case "Float": 
                $type = "Float";
                $type_color = "#0099c5";
                $type_length = strlen($data);
                $type_data = htmlentities($data); break;

            case "Integer": 
                $type_color = "red";
                $type_length = strlen($data);
                $type_data = htmlentities($data); break;

            case "Boolean": 
                $type_color = "#92008d";
                $type_length = strlen($data);
                $type_data = $data ? "TRUE" : "FALSE"; break;

            case "NULL": 
                $type_length = 0; break;

            case "Array": 
                $type_length = count($data);
        }

        if (in_array($type, array("Object", "Array"))) {
            $notEmpty = false;

            foreach($data as $key => $value) {
                if (!$notEmpty) {
                    $notEmpty = true;

                    if ($isTerminal) {
                        echo $type . ($type_length !== null ? "(" . $type_length . ")" : "")."\n";

                    } else {
                        $id = substr(md5(rand().":".$key.":".$level), 0, 8);

                        echo "<a href=\"javascript:toggleDisplay('". $id ."');\" style=\"text-decoration:none\">";
                        echo "<span style='color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>";
                        echo "</a>";
                        echo "<span id=\"plus". $id ."\" style=\"display: " . ($collapse ? "inline" : "none") . ";\">&nbsp;&#10549;</span>";
                        echo "<div id=\"container". $id ."\" style=\"display: " . ($collapse ? "" : "inline") . ";\">";
                        echo "<br />";
                    }

                    for ($i=0; $i <= $level; $i++) {
                        echo $isTerminal ? "|    " : "<span style='color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;";
                    }

                    echo $isTerminal ? "\n" : "<br />";
                }

                for ($i=0; $i <= $level; $i++) {
                    echo $isTerminal ? "|    " : "<span style='color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;";
                }

                echo $isTerminal ? "[" . $key . "] => " : "<span style='color:black'>[" . $key . "]&nbsp;=>&nbsp;</span>";

                call_user_func($recursive, $value, $level+1);
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
                        $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "  " : 
                        "<span style='color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>&nbsp;&nbsp;";
            }

        } else {
            echo $isTerminal ? 
                    $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "  " : 
                    "<span style='color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>&nbsp;&nbsp;";

            if ($type_data != null) {
                echo $isTerminal ? $type_data : "<span style='color:" . $type_color . "'>" . $type_data . "</span>";
            }
        }

        echo $isTerminal ? "\n" : "<br />";
    };

    call_user_func($recursive, $input);
}
