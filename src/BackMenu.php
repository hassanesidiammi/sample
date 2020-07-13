<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class BackMenu extends Menu {

    public function __construct($selected) {
        $this->baseUrl    = Configuration::get('baseUrl');

        foreach (Configuration::get('BackMenu') as $key => $value) {
            $this->menuLabels[$key] = $value['label'];
            $this->pagesMenu[$key]  = $key;
            if(array_key_exists('children', $value)) {
                foreach ($value['children'] as $key2 => $value2) {
                    $this->menuLabels[$key2] = $value2['label'];
                    $this->pagesMenu[$key2]  = $key;
                }
            }
        }

        $this->selected   = array_key_exists($selected, $this->pagesMenu) ? $this->pagesMenu[$selected] : false;
    }

    public function url($page, $module='index', $get = [], $segment=null) {
        $module = $module ?: 'index';
        $query = http_build_query(array_merge(
            [
                'page'   => $page,
                'module' => $module,
            ],
            $get
        ));

        return $this->baseUrl.'/admin.php' .($query ? '?'.$query : '').($segment ? '#'.$segment : '');
    }
}