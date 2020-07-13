<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class Menu {
    use UrlGeneratorTrait;

    protected $selected;
    protected $menuLabels;
    protected $pagesMenu;

    public function __construct($selected) {
        $this->baseUrl    = Configuration::get('baseUrl');
        $this->menuLabels = Configuration::get('MenuLabels');
        $this->pagesMenu  = Configuration::get('pagesMenu');
        $this->selected   = array_key_exists($selected, $this->pagesMenu) ? $this->pagesMenu[$selected] : false;
    }

    /**
     * @return string[]
     */
    public function getPagesMenu()
    {
        return $this->pagesMenu;
    }

    /**
     * @return string[]
     */
    public function getMenuLabels()
    {
        return $this->menuLabels;
    }

    /**
     * @return string[]
     */
    public function getItems()
    {
        return $this->getMenuLabels();
    }

    /**
     * @return string
     */
    public function getSelected()
    {
        return $this->selected;
    }

    public function render() {
        $menu = $this;
        require 'Views/_fragments/menu.php';
    }

    public function isActive($page) {
        return $this->selected == $page;
    }
}