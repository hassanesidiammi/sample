<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?php echo Configuration::get('baseUrl'); ?>/img/logo.png" />
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-select.css">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/tabs.css">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/tabs2.css">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/tabs3.css">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/button.css">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/global.css">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/datepicker.css">
    <style>
        pre {
            max-width: 95%;
        }
        pre .text {
            white-space: normal;
        }
    </style>

    <script type="text/javascript" src="bootstrap/js/jquery.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap-select.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/myscript2.js"></script>
    <script type="text/javascript" src="bootstrap/js/modules/global.js"></script>
    <title>Siron</title>
</head>

<body class="bg-cyan">
<?php
/** @var Menu $menu */
/** @var Views $view */
/** @var Session $session */
?>
<div id='header'>
    <a href="<?php echo Configuration::get('baseUrl'); ?>" target="cadre1">
        <img SRC="img/logo.png" style='float:right;margin-top:-50px;width:70px;margin-right:15px'></a>
</div>
<div class="body">
    <div class="container-fluid">
        <div style='margin-top:-34px;margin-left:8px;'
             class="sky-tabs sky-tabs-pos-top-left sky-tabs-anim-flip sky-tabs-response-to-icons">
            <?php if ($menu && $menu instanceof Menu) $menu->render(); ?>
            <div class="container-fluid">
                <pre class="text-danger mt-2 mb-2" style="padding: 1rem; line-height: 2rem; font-size: 1.5rem;"><?php
                    echo str_replace(
                            [
                                Configuration::get('baseDir').'/',
                                Configuration::get('baseDir').'\\',
                            ],
                            '',
                            $exception->getMessage()
                        ). PHP_EOL;
                    echo '<code>' . $file . ':' . $exception->getLine() . '</code>' . PHP_EOL;
                    echo '<div style="padding-left: 3rem;">';
                    echo '<span style="font-size: 1rem">'.PHP_EOL.'Details:</span>';
                    foreach ($traces as $trace) {
                        echo PHP_EOL.'<code>' . $trace['file'] . ':'.(array_key_exists('line', $trace)?$trace['line']:'').'</code>';
                        echo '    <code>' .PHP_EOL.'    '. (array_key_exists('class', $trace)?$trace['class']:'') . ':'. $trace['function'].'(';
                        if(array_key_exists('args', $trace) && array_key_exists(0, $trace['args']) && !empty($trace['args'][0])){
                            $l = 0;
                            foreach ($trace['args'] as $arg) {
                                $l++;
                                echo PHP_EOL.'        ';
                                if (is_array($arg)) {
                                    echo '[';
                                    $i = 1;
                                    foreach ($arg as $item) {
                                        if (is_array($item)) {
                                            $item = count($item) ? '[...]' : '[]';
                                        } elseif (is_object($item)) {
                                            $item = get_class($item);
                                        } elseif (is_bool($item)) {
                                            $item = $item ? 'TRUE' : 'FALSE';
                                        } else {
                                            $item = "\"$item\"";
                                        }

                                        echo $i > 1 ? ', ' : '';
                                        echo $item;
                                        if (++$i > 3) {
                                            break;
                                        }
                                    }
                                    echo count($arg) > 3 ? ',...' : '';
                                    echo ']';
                                } elseif (is_object($arg)) {
                                    echo get_class($arg);
                                } elseif (is_bool($arg)) {
                                    echo $arg ? 'TRUE' : 'FALSE';
                                } elseif (is_string($arg)) {
                                   echo '"'.substr($arg, 0, 50).(strlen($arg)>50 ? '...' : '').'"';
                                }else {
                                    echo $arg;
                                }
                                echo ($l > 1 && $l < count($trace['args']) ) ? ',' : '';
                            }
                        }
                        echo PHP_EOL.'    )</code>';
                    }
                    echo '</div>';
                    echo '<div style="padding-left: 3rem;">';
                    echo '<span style="font-size: 1rem">'.PHP_EOL.'Logs:</span>'.PHP_EOL;
                    echo $tracesAsString;
                    echo '</div>';
                    ?></pre>
            </div>
        </div>
    </div>
</div>

<?php if (isset($page)) echo $page; ?>
</body>
</html>