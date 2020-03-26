<?php


namespace PHP7API\Connection;

class MysqlResultRow extends MysqlResult {

    public function getAll(){
        return get_object_vars($this);
    }
}
