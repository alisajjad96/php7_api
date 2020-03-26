<?php


namespace PHP7API\Components;


class Component implements \PHP7API\Component {

    protected $currentRoute = null;

    /**
     * @return array|null
     */
    public function getCurrentRoute():? array {
        return $this->currentRoute;
    }

    /**
     * @param array $currentRoute
     */
    public function setCurrentRoute(array $currentRoute): void{
        $this->currentRoute = $currentRoute;
    }


}
