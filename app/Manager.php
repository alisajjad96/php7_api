<?php

namespace PHP7API\App;

/**
 * Class Manager
 * @package App
 */
class Manager{
    /**
     * @var array Inputs received
     */
    protected $requestData;
    /**
     * @var Auth Auth Manager
     */
    protected $authManager;
    /**
     * @var mixed Route Given in Input
     */
    protected $route;
    /**
     * @var bool|string|float false if not started, micro time if started
     */
    protected $started;
    /**
     * @var array All App routes from routes.php
     */
    protected $routes;
    /**
     * @var array Current Route
     */
    protected $currentRoute;
    /**
     * @var array Response returned by Component
     */
    protected $response;

    /**
     * Manager constructor.
     */
    public function __construct(){
        $this->requestData = getInputData();
        $this->route = $this->requestData['route'] ?? null;
        $this->routes = require_once MAIN_PATH.'routes.php';
        $this->authManager = new Auth();

        if (Config::config('debug', 'environment')):
            error_reporting(-1);
            ini_set('display_errors', 'On');
        endif;
    }

    /**
     * Starts the app
     * @return bool true
     */
    public function start(){
        if (!empty($this->started)):
            return false;
        endif;
        $this->started = microtime();

        $this->sendHeaders();

        $this->authManager->validate() || sendUnauthorized();
        $this->parseRoute();
        $this->callRoute();
        $this->parseResponse();

        return true;
    }
    /**
     * sets API headers
     */
    public function sendHeaders(){
        sendHeader('Access-Control-Allow-Origin: *');
        sendHeader('Content-Type: application/json; charset=utf-8');
        sendHeader('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        sendHeader('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Origin, Accept, Access-Control-Allow-Methods, Authorization, X-Requested-With');
        sendHeader('Cache-Control: no-cache, must-revalidate, max-age=0');
    }
    /**
     *  Parses All the Routes with Route given in Input
     */
    protected function parseRoute(){

        if (empty($this->route) || empty($this->routes) || !is_array($this->routes)):
            unknownRoute();
        endif;

        foreach ($this->routes as $name => $route):
            if ($this->route != $name):
                continue;
            endif;
            if (!empty($route['requires']) && !checkRequiredInputs($route['requires'], $this->requestData)):
                $stringRequires = implode(', ', $route['requires']);
                sendJSON([
                    'success' => 0,
                    'message' => "This Route requires ({$stringRequires}) inputs",
                ]);
            endif;
            $this->currentRoute = [
                'name' => $name,
                'class' => $route['class'] ?? '',
                'method' => $route['method'] ?? ''
            ];
        endforeach;
    }
    /**
     *  Calls Current Route Component
     */
    protected function callRoute(){
        if (empty($this->currentRoute)):
            unknownRoute();
        endif;

        if (!class_exists($this->currentRoute['class'])):
            debugDump("Class['{$this->currentRoute['class']}'] doesn't exists");
        endif;

        if (!method_exists($this->currentRoute['class'], $this->currentRoute['method'])):
            debugDump("Class['{$this->currentRoute['class']}'] method['{$this->currentRoute['method']}'] doesn't exists");
        endif;

        $class = new $this->currentRoute['class']();
        $class->setCurrentRoute($this->currentRoute);
        $this->response = $class->{$this->currentRoute['method']}($this->requestData, $this->route);
    }
    /**
     *  Sends the json response returned by Component
     */
    protected function parseResponse(){
        if (is_null($this->response) || !is_array($this->response)):
            unknownResponse();
        endif;
        /**
         * Adds Required response values if missing
         */
        sendJSON(array_merge([
            'success' => 0,
            'message' => 'Response not found',
            'time' => date('Y-m-d H:i:s')
        ], $this->response));
    }

    /**
     * @return array
     */
    public function getRequestData(): array{
        return $this->requestData;
    }

    /**
     * @param array $requestData
     */
    public function setRequestData(array $requestData): void{
        $this->requestData = $requestData;
    }

    /**
     * @return mixed
     */
    public function getRoute(){
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route): void{
        $this->route = $route;
    }

    /**
     * @return array
     */
    public function getRoutes(): array{
        return $this->routes;
    }

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes): void{
        $this->routes = $routes;
    }

    /**
     * @return array
     */
    public function getCurrentRoute(): array{
        return $this->currentRoute;
    }

    /**
     * @param array $currentRoute
     */
    public function setCurrentRoute(array $currentRoute): void{
        $this->currentRoute = $currentRoute;
    }
}
