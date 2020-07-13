<?php

/**
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

abstract class BaseController {
    use UrlGeneratorTrait;

    protected $viewsDir;
    protected $menu;
    protected $connection;
    protected $session;
    protected $modificationManager;
    protected $env = 'dev';

    public function __construct($menu) {
        $this->viewsDir = basename(__DIR__);
        $this->menu = $menu;

        $this->connection = Configuration::getConnection();
        $this->baseUrl    = Configuration::get('baseUrl');
        $this->session    = Session::start();
    }

    public function getUsername() {
        return $this->session->getUsername();
    }

    protected function grantAccess() {
        if (Configuration::is('disable_security_login')) {
            return;
        }
        if(!$this->getUsername()) {
            throw new SecurityException();
        }
    }

    public function redirect($page, $module='index', $get = [], $segment=null) {
        header('Location: '.$this->url($page, $module, $get, $segment));
        die;
    }

    public function isDev() {
        return 'dev' === $this->env;
    }

    protected function getPeriodsRange($start, $end) {
        if(empty($start) || empty($end)){
            return [];
        }
        $yearStart  = (int) substr($start, 0, 4);
        $yearEnd    = (int) substr($end, 0, 4);
        $monthStart = (int) substr($start, 4, 2);
        $monthEnd   = (int) substr($end, 4, 2);
        $periodsRange = [];
        foreach (range($yearStart, $yearEnd) as $year) {
            foreach (range(1, 12) as $month) {
                $periodsRange[] = sprintf('%02d%02d', $year, $month);
            }
        }
        if ($monthStart > 1) {
            $periodsRange = array_slice($periodsRange,$monthStart -1);
        }
        if ($monthEnd < 12) {
            $periodsRange = array_slice($periodsRange,0, count($periodsRange) - (12 - $monthEnd));
        }

        return $periodsRange;
    }
}