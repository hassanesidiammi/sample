<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class Session {
    private static $session;
    private $user;

    private function __construct() {
        session_start();
        $this->user = $this->get('login', false);
    }

    public function get($name, $default=null){
        return array_key_exists($name, $_SESSION) ? $_SESSION[$name] : $default;
    }

    public function set($name, $value){
        $_SESSION[$name] = $value;

        return $this;
    }

    public static function start() {
        if (!self::$session) {
            self::$session = new self();
        }

        return self::$session;
    }

    public function getUsername()
    {
        return $this->user;
    }

    public function addMessageFlash($message, $status='info') {
        $_SESSION['flashes'][$status][] = $message;

        return $this;
    }

    public function hasMessageFlash($status='info') {
        return
            array_key_exists('flashes', $_SESSION) &&
            array_key_exists($status, $_SESSION['flashes']) &&
            count($_SESSION['flashes'][$status]);
    }

    public function getMessageFlash($status='info') {
        if (!$this->hasMessageFlash($status)) {
            return false;
        }
        $messages = $_SESSION['flashes'][$status];
        unset($_SESSION['flashes'][$status]);

        return $messages;
    }

    public function __destruct() {
    }
}