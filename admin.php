<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

require_once 'config/bootstrap.php';

$session = Session::start();
$menu = false;
try {
    $mysqli = @Configuration::getConnection();

    if (isset($_GET["page"])) {
        ob_start();
        $module = (isset($_GET["module"])) ? $_GET["module"] : 'index';
        $action = $module.'Action';
        $controllerName = ucfirst($_GET["page"]).'Controller';

        $menu       = new BackMenu(ucfirst(trim($_GET["page"])));
        $menu->isBackend = true;
        $controller = new $controllerName($menu);
        $controller->isBackend = true;
        $params     = $controller->{$action}();
        $params['action'] = $module;

        if (!array_key_exists('viewName', $params)) {
            $params['viewName'] = $module.'.php';
        }
        $view = new Views(ucfirst($_GET["page"]), $params, 'Back');
        $view->isBackend = true;

        if (!file_exists('src/Views/Back/'.$view->getPage().'/_layout.php')) {
            throw new FileNotFoundException('File view not found!'.PHP_EOL.'src/Views/'.$view->getPage().'/_layout.php'.PHP_EOL);
        }

        if (!file_exists('src/Views/'.$view->getViewPath())) {
            throw new FileNotFoundException('File view not found!'.PHP_EOL.'src/Views/'.$view->getViewPath().PHP_EOL);
        }

        require_once 'src/Views/layout_admin.php';

        echo ob_get_clean();
    }else {
        header('Location: '.Configuration::get('baseUrl').'/admin.php?page=Configuration');
    }
}catch (SecurityException $exception) {
    header('Location: '.Configuration::get('baseUrl').'/login.php');
}catch (Exception $exception) {
    $page = ob_get_clean();
    $file = substr($exception->getFile(), strlen(Configuration::get('baseDir')) + 1);
    $traces = array_map(
        function ($trace) {
            $trace['file'] = array_key_exists('file', $trace) ? substr($trace['file'], strlen(Configuration::get('baseDir')) + 1):null;

            return $trace;
        }, $exception->getTrace()
    );

    $tracesAsString = str_replace(
        [
            Configuration::get('baseDir').'/',
            Configuration::get('baseDir').'\\',
        ],
        '',
        $exception->getTraceAsString()
    );

    require_once 'src/Views/layout_exception.php';
}
