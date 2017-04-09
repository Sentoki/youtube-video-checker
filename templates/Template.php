<?php

namespace PetrovEgor\templates;

class Template {
    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $template;

    /**
     * @var self
     */
    private static $instance;

        
    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function render()
    {
        require_once $this->template;
    }
}
