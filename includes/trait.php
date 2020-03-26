<?php

namespace PHP7API;

trait Instance{
    private static $instance;

    /**
     * Instance constructor.
     *
     * @return static
     */
    public final static function instance(){
        if(!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Sets the instance to given object
     * @param $instance
     * @return mixed
     */
    public static function setInstance( $instance ){
        return self::$instance = $instance;
    }
}

trait ArrayOrJson{

    /**
     * Converts object to array
     *
     * @return array
     */
    public function toArray() : array{
        return get_object_vars( $this );
    }

    /**
     * Converts object to json
     *
     * @return string
     */
    public function toJson() : string
    {
        return json_encode( $this->toArray() );
    }

    /**
     * Convert instance object to array
     *
     * @return array
     */
    public static function instanceToArray() : array{

        if(!method_exists(self::class,'instance')):
            return [];
        endif;

        return self::instance()->toArray();
    }

    /**
     * Convert instance object to json
     *
     * @return string
     */
    public static function instanceToJson() : string{

        if(!method_exists(self::class,'instance')):
            return json_encode([]);
        endif;

        return json_encode( self::instanceToArray() );
    }
}
