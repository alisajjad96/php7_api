<?php

namespace PHP7API\Connection;

use PHP7API\App\Base;
use PHP7API\ArrayOrJson;

class MysqlResult extends Base implements \Serializable {
    use ArrayOrJson;

    /**
     * @inheritDoc
     */
    public function serialize(){
        return serialize($this->getAll());
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized){
        return unserialize($serialized);
    }

    public function __toString(){
        return $this->toJson();
    }
}
