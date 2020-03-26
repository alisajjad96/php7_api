<?php


namespace PHP7API\App;

/**
 * Class Base of App
 * @package App
 */
class Base extends \stdClass {
    /**
     * Sets Object Property via magic Method
     * @param $a
     * @param $b
     */
    public function __set($a, $b){
        $this->$a = $b;
    }

    /**
     * Gets Object Property via magic Method
     * @param $a
     * @return |null
     */
    public function __get($a){
        return $this->$a ?? null;
    }

    /**
     * Checks if Object property exists
     * @param $a
     * @return bool
     */
    public function __isset($a){
        return isset($this->$a);
    }

    /**
     * Un-sets Object property
     * @param $a
     */
    public function __unset($a){
        unset($this->$a);
    }
}
