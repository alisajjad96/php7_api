<?php

namespace PHP7API;

interface Component{

}

interface Crud {
    /**
     * Creates a record in DB
     * @param $request
     * @return array
     */
    public function create($request) : array;
    /**
     * Reads single or multiple records in DB
     * @param $request
     * @return array
     */
    public function read($request) : array;
    /**
     * Updates a record in DB
     * @param $request
     * @return array
     */
    public function update($request) : array;
    /**
     * Deletes a record in DB
     * @param $request
     * @return array
     */
    public function delete($request) : array;
}


interface DB{
    /**
     * Opens the connections to DB
     *
     * @return bool
     */
    public function openConnection(): bool;
    /**
     * Closes the connections to DB
     *
     * @return bool
     */
    public function closeConnection(): bool;
    /**
     * Executes the given query
     *
     * @param string $query query to perform
     * @param bool $openConnection open the connection if not alive
     *
     * @return bool
     */
    public function exec(string $query, bool $openConnection = true): bool;

    /**
     * Executes the given query
     *
     * @param string $query query to perform
     * @param array $values values in queries
     * @param string $binds binds of values
     * @param bool $openConnection open the connection if not alive
     *
     * @return \PHP7API\Connection\MysqlResultCollection|null
     */
    public function fetch(string $query, $values = [], $binds = '', bool $openConnection = true) : ?\PHP7API\Connection\MysqlResultCollection;
    /**
     * Converts the array to MysqlResultCollection object
     *
     * @param \mysqli_result $result
     * @return \PHP7API\Connection\MysqlResultCollection
     *
     */
    public function fetchResultAsObject(\mysqli_result $result): \PHP7API\Connection\MysqlResultCollection;
    /**
     * Checks if connection to DB is open
     *
     * @return bool
     */
    public function isConnectionOpen(): bool;
    /**
     * Returns the DB connection Object
     *
     * @return \mysqli|null
     */
    public function getConnectionObject(): ?mysqli;
    /**
     * Returns the DB Host
     *
     * @return string
     */
    public function getHost(): string;
    /**
     * Returns the DB Username
     *
     * @return string
     */
    public function getUsername(): string;
    /**
     * Returns the DB Password
     *
     * @return string
     */
    public function getPassword(): string;
    /**
     * Returns the DB Name
     *
     * @return string
     */
    public function getDBName(): string;
    /**
     * Returns the DB Port
     *
     * @return int
     */
    public function getPort(): int;
    /**
     * Returns the DB Socket
     *
     * @return string
     */
    public function getSocket(): string;
}
