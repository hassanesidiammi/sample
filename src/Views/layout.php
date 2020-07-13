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
<div id='header' class="container-fluid">
    <div class="row">
        <a href="<?php echo Configuration::get('baseUrl'); ?>" target="cadre1" class="logo pull-right">
            <img SRC="img/logo.png" style="margin-top:20px;width:70px;margin-right:15px">
        </a>
    </div>
</div>

<div class="container-fluid">
    <div style="margin-top:20px;"
         class="sky-tabs sky-tabs-pos-top-left sky-tabs-anim-flip sky-tabs-response-to-icons">
        <?php $menu->render(); ?>
        <?php require_once $view->getPage().'/_layout.php'; ?>
    </div>
</div>
</body>
</html>