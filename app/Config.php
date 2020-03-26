<?php


namespace PHP7API\App;

/**
 * Class Config
 *
 * @package App
 */
class Config{

    /**
     * @var array All configurations variables from configs.php
     */
    protected static $configs = [];
    /**
     * @var bool Initiated Checker
     */
    protected static $initiated = false;

    /**
     * Initializes Configuration Object
     */
    public static function init(){
        $configurations = [];
        if (file_exists(MAIN_PATH.'configs.php')):
            $configurations = require_once MAIN_PATH.'configs.php';
        endif;

        /**
         * Default Configurations
         */
        static::$configs = array_merge([
            'db' => [
                'host' => null,
                'user' => null,
                'password' => null,
                'prefix' => null,
                'db' => null,
                'port' => null,
                'socket' => null,
            ],
            'environment' => [
                'debug' => true,
                'url' => '',
            ],
            'auth' => [
                'type' => 'none'
            ]
        ], $configurations);

        static::$initiated = true;
    }

    /**
     * Returns Configuration value or type of configuration
     *
     * @param string|null $config null if want to get whole type of configuration, string value otherwise
     * @param null $type type of configuration e.g auth, environment
     * @return array|string|null
     */
    public static function config($config, $type){
        if (!static::$initiated):
            static::init();
        endif;
        if (is_null($config)):
            return static::$configs[$type] ?? [];
        endif;
        return !empty(static::$configs[$type]) ? static::$configs[$type][$config] ?? null : null;
    }
}
