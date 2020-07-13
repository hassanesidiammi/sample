<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class Views {
    use UrlGeneratorTrait;

    private $data;
    private $viewName;
    private $page;
    /**
     * @var string
     */
    private $domain;

    public function __construct($page, $data=[], $domain=false) {
        $this->page = $page;
        $this->data    = $data;
        $this->baseUrl = Configuration::get('baseUrl');
        $this->domain  = $domain ?: '';
        $this->setViewName($data['viewName']);

    }

    public function exists($name) {
        return array_key_exists($name, $this->data);
    }

    public function is($name) {
        return array_key_exists($name, $this->data) && $this->data[$name];
    }

    public function isEmpty($name) {
        return !$this->is($name) || empty($this->data[$name]);
    }

    public function count($name) {
        return $this->is($name) ? count($this->data[$name]) : false;
    }

    public function __get($name) {
        if(!$this->exists($name)) {
            throw new VariableNotFoundException([$name, $this->data]);
        }
        return $this->data[$name];
    }

    public function render() {
        $view = $this;
        require 'Views/'.$this->getViewPath(true);
    }

    public function __toString()
    {
        ob_start();
        $this->render();
        return ob_get_clean();
    }

    public function getViewName()
    {
        return $this->viewName;
    }

    public function setViewName($viewName) {
        $path = ($this->domain ? $this->domain.'/' : '' ).$this->page.'/'.$viewName;
        if (!file_exists(__DIR__.'/Views/'.$path)) {
            throw new FileNotFoundException('File view not found!'.PHP_EOL.$path);
        }
        $this->viewName = $viewName;

        return $this;
    }

    public function getViewPath() {
        return ($this->domain ? $this->domain.'/' : '' ).$this->page.'/'.$this->viewName;
    }

    public function getPage($withDomain=false) {
        return $withDomain ? (($this->domain ? $this->domain.'/' : '' ).$this->page) : $this->page;
    }

    public static function selectOptions($options, $chosen, $enableChecked=true){
        $optionsString = '';
        foreach ($options as $key => $value) {
            $optionsString .= '<option value="'.$key.'"';
            if ($enableChecked && $key == $chosen){
                $optionsString .= ' selected="selected"';
            }
            $optionsString .= '>'.htmlentities(utf8_decode($value)).'</option>';
        }

        return $optionsString;
    }
}