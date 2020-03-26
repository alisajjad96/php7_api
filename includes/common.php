<?php
define('MAIN_PATH', substr(__DIR__, 0,-8));

if (!function_exists('sendUnauthorized')):
    /**
     * Sends Response of unauthorized.
     */
    function sendUnauthorized(){
        $authType = ucwords(PHP7API\App\Config::config('type', 'auth'));
        sendHeader('HTTP/1.1 401 Authorization Required');
        if ($authType == 'Digest'):
            $realm = PHP7API\App\Config::config('realm', 'auth');
            $nonce = uniqid();
            $opaque = md5($realm);
            sendHeader("WWW-Authenticate: {$authType} realm=\"{$realm}\",qop=\"auth\",nonce=\"{$nonce}\",opaque=\"{$opaque}\"");
        else:
            sendHeader("WWW-Authenticate: {$authType} realm=\"Access denied\"");
        endif;

        sendJSON([
            'success' => 0,
            'message' => 'unauthorized'
        ]);
    }
endif;

if (!function_exists('arrayToObject')):
    /**
     * Converts array to Base Class object recursively
     */
    function arrayToObject(array $array) {
        $obj = new PHP7API\App\Base;
        foreach($array as $name => $value):
            if(is_string($name) && !empty($name)):
                if(is_array($value)):
                    $obj->{$name} = arrayToObject($value);
                else:
                    $obj->{$name} = $value;
                endif;
            endif;
        endforeach;
        return $obj;
    }
endif;


if (!function_exists('getInputData')):
    /**
     * Returns the inputs
     */
    function getInputData(){
        //if input from request (Get, Post)
        $data = $_REQUEST;
        if( empty($data) ):
            //Get from PHP input
            $postData = file_get_contents("php://input");

            if (empty($postData)):
                return [];
            endif;
            //if in json format
            $data = @json_decode($postData, true);

            //otherwise, check for query param format e.g name=value&name2=value
            if (empty($data)):
                $data = [];
                $array = explode('&', $postData);
                foreach ($array as $value):
                    $postArr = explode('=', $value);
                    if (!empty($postArr[0])):
                        $data[$postArr[0]] = $postArr[1] ?? null;
                    endif;
                endforeach;
            endif;
        endif;
        if(!is_array($data)):
            return [];
        endif;

        return $data;
    }
endif;

if (!function_exists('checkRequiredInputs')):
    /**
     * checks if given array has all required inputs
     */
    function checkRequiredInputs($requires, $inputs){
        return count(array_intersect($requires, array_keys($inputs))) == count($requires);
    }
endif;

if (!function_exists('sendJSON')):
    /**
     * sends the json response
     */
    function sendJSON( $data ){

        if( !is_array( $data ) ):
            return false;
        endif;
        sendHeader('Content-Type: application/json;charset=UTF-8');
        echo json_encode( $data );
        if(!defined('PHPUNIT_TESTSUITE')):
            die();
        endif;
    }
endif;

if (!function_exists('unknownRoute')):
    /**
     * sends the Route not found response
     */
    function unknownRoute(){
        return sendJSON([
            'success' => 0,
            'message' => 'Unknown Route'
        ]);
    }
endif;

if (!function_exists('unknownResponse')):
    /**
     * sends the Unknown Response
     */
    function unknownResponse(){
        return sendJSON([
            'success' => 0,
            'message' => 'Unknown Response'
        ]);
    }
endif;

/***
 *
 *
 *
 *  DEBUG
 *
 *
 */
if (!function_exists('debugDump')):
    /**
     * Var dumps the given variables if environment debug mode is true
     */
    function debugDump(...$vars){
        if(!PHP7API\App\Config::config('debug', 'environment')):
            die();
        endif;
        echo '<pre style="text-align: left;letter-spacing: 1px;line-height: 1.2rem;">';
        $bt = debug_backtrace();
        $caller = array_shift($bt);
        $fileArr = explode('/', $caller['file'] ?? '');
        $file = $fileArr[sizeof($fileArr)-1] ?? '';
        $line = $caller['line'] ?? '';
        foreach ($vars as $var):
            if( is_array( $var ) || !is_object( $var ) ):
                //array_walk_recursive($var, "htmlArrayFilter");
            elseif( is_string( $var ) ):
                $var = htmlspecialchars( $var, ENT_QUOTES, 'UTF-8' );
            endif;
            echo '<hr />';
            echo  "[File:{$file}][LINE:{$line}]: ";
            var_dump( $var );
            echo '<hr />';
        endforeach;
        echo '</pre>';
    }
endif;

if (!function_exists('debugPrint')):
    /**
     * Prints the given variables if environment debug mode is true
     */
    function debugPrint(...$vars){
        if(!PHP7API\App\Config::config('debug', 'environment')):
            die();
        endif;
        $bt = debug_backtrace();
        $caller = array_shift($bt);
        $fileArr = explode('/', $caller['file'] ?? '');
        $file = $fileArr[sizeof($fileArr)-1] ?? '';
        $line = $caller['line'] ?? '';
        foreach ($vars as $var):
            $var = htmlspecialchars( $var, ENT_QUOTES, 'UTF-8' );
            echo  "[File:{$file}][LINE:{$line}]: ";
            echo $var. '<br />';
        endforeach;
    }
endif;

if (!function_exists('sendHeader')):
    /**
     * Prints the given variables if environment debug mode is true
     */
    function sendHeader($header){

        if(defined('PHPUNIT_TESTSUITE') && PHPUNIT_TESTSUITE):
            return false;
        endif;
        header($header);
        return true;
    }
endif;

