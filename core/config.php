<?php

class Config {
    private static $instance;

    public function __construct() {

    }
    
    /**
     * get config value
     *
     * @param string $cfgtype: config type, value: local, remote
     * @param string $tag
     * @return mixed
     */
    public static function get($tag) {
        $obj = self::getInstance();
        if (!isset ($obj->$tag)) {
            include CONFIG_PATH.substr($tag,0,strpos($tag,'.')).'.php';
            foreach ($config as $configname => $configvalue) {
                $obj->$configname = $configvalue;
            }
        }

        return $obj->$tag;
    }

    private static function getInstance() {
        if(!is_object(self::$instance)) {
            self::$instance = new Config();
        }

        return self::$instance ;
    }

    /**
     * Overloading function, can't call direct
     */
    public function __set($tag, $value) {
        $this->$tag = $value;
    }

    public function __get($tag) {
        return $this->$tag;
    }
}
?>