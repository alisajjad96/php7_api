<?php

namespace PHP7API\Connection;

class MysqlResultCollection extends MysqlResult implements \Iterator {

    protected $rows = [];
    protected $index = 0;

    public function add(MysqlResultRow $row){
        $this->rows[] = $row;
        return $this;
    }

    public function addMultiple(array $rows){
        array_push($this->rows, $rows);
        return $this;
    }

    public function pop(){
        return array_pop($this->rows);
    }

    public function keys(){
        return array_keys($this->rows);
    }

    public function reverse(){
        return array_reverse($this->rows);
    }

    public function values(){
        return array_values($this->rows);
    }

    public function merge(MysqlResultCollection $toMerge, $atEnd = true){
        if ($atEnd):
            $this->rows = array_merge($this->rows, $toMerge->getAll());
        else:
            $this->rows = array_merge($toMerge->getAll(), $this->rows);
        endif;
        return $this->rows;
    }

    public function getAll(){
        return $this->rows;
    }

    public function getRowsNum(){
        return count($this->rows);
    }

    public function isEmpty(){
        return empty($this->rows);
    }
    public function first(){
        return $this->nth(0);
    }

    public function nth(int $nth){
        return $this->rows[$nth] ?? null;
    }

    public function last(){
        return $this->nth(count($this->rows) -1 );
    }

    public function __get($a){
        return !empty($row = $this->first()) ? $row->$a : $this->$a ?? null;
    }

    /**
     * @inheritDoc
     */
    public function current(){
        return $this->nth($this->index);
    }

    /**
     * @inheritDoc
     */
    public function next(){
        return $this->nth(++$this->index);
    }

    /**
     * @inheritDoc
     */
    public function prev(){
        return $this->nth(--$this->index);
    }

    /**
     * @inheritDoc
     */
    public function key(){
        return $this->index;
    }

    /**
     * @inheritDoc
     */
    public function valid(){
        return isset($this->rows[$this->key()]);
    }

    /**
     * @inheritDoc
     */
    public function rewind(){
        $this->index = 0;
    }
}
