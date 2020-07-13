<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

trait UrlGeneratorTrait {
    protected $baseUrl;
    public $isBackend;

    public function url($page, $module='index', $get = [], $segment=null) {
        $module = $module ?: 'index';
        $query = http_build_query(array_merge(
            [
                'page'   => $page,
                'module' => $module,
            ],
            $get
        ));
        return $this->baseUrl.($this->isBackend ? '/admin.php' : '/index.php') .($query ? '?'.$query : '').($segment ? '#'.$segment : '');
    }
}