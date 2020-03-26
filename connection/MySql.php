<?php
/**
|--------------------------------------------------------------------------
| Main Database File
|--------------------------------------------------------------------------
 */

namespace PHP7API\Connection;

use PHP7API\App\Config;
use PHP7API\ArrayOrJson;
use PHP7API\Instance;

class MySql implements \PHP7API\DB {
    use Instance, ArrayOrJson;
    /**
     * @var \mysqli|false
     */
    public $conn;
    /**
     *
     * @var string <p>Host Name</p>
     */
    protected $host;
    /**
     *
     * @var string <p>User Name</p>
     */
    protected $user;
    /**
     *
     * @var string <p>User Password</p>
     */
    protected $password;
    /**
     *
     * @var string <p></p>
     */
    protected $dbName;
    /**
     *
     * @var int
     */
    protected $port;
    /**
     *
     * @var string
     */
    protected $socket;
    /**
     * @var int <p>Total Queries Performed</p>
     */
    public $queriesPerformed;
    /**
     *
     * @var string
     */
    public $error;
    /**
     *
     * @var array
     */
    public $error_list;
    /**
     *
     * @var int
     */
    public $error_num;

    public function __construct(string $host = null, string $user = null,
                                string $password = null, string $db = null,
                                string $prefix = null, int $port = null, string $socket = null ){

        $this->host = $host ?? Config::config('host', 'db');
        $this->user = $user ?? Config::config('user', 'db');
        $this->password = $password ?? Config::config('password', 'db');
        $this->dbName = $db ?? Config::config('db', 'db');
        $this->port = $port ?? Config::config('port', 'db') ?? ini_get('mysqli.default_port');
        $this->socket = $socket ?? Config::config('socket', 'db') ?? ini_get('mysqli.default_socket');
    }
    public static function init(string $host = null, string $user = null,
                                string $password = null, string $db = null,
                                string $prefix = null, int $port = null, string $socket = null ){
        $instance = new MySql();
        static::setInstance($instance);
    }
    /**
     * Opens the connections to DB
     *
     * @return bool
     */
    public function openConnection(): bool{
        if( !empty( $this->conn ) && $this->conn->ping() ):
            return $this->conn;
        endif;
        $this->conn = new \mysqli( $this->getHost(), $this->getUsername(),
            $this->getPassword(), $this->getDBName(), $this->getPort(), $this->getSocket() );
        return !empty($this->conn);
    }
    /**
     * Closes the connections to DB
     *
     * @return bool
     */
    public function closeConnection(): bool{
        $success = false;
        if( !empty( $this->conn )  ):
            $success = $this->conn->close();
            unset( $this->conn );
        endif;
        return $success;
    }
    /**
     * @param string $sql
     * @param bool $openConnection
     *
     * @return bool|\mysqli_stmt
     */
    public function prepare(string $sql, $openConnection = true){
        if(!$this->isConnectionOpen()):
            if( $openConnection ):
                $this->openConnection();
            else:
                return false;
            endif;
        endif;
        $stmt = $this->conn->prepare( $sql );
        if(!$stmt):
            $this->error = $this->conn->error;
            $this->error_list = $this->conn->error_list;
            $this->error_num = $this->conn->errno;
            return false;
        endif;
        return $stmt;
    }
    /**
     * Executes the given query
     *
     * @param string $query query to perform
     * @param bool $openConnection open the connection if not alive
     *
     * @return bool
     */
    public function exec(string $query, bool $openConnection = true): bool{
        if(!$this->isConnectionOpen()):
            if( $openConnection ):
                $this->openConnection();
            else:
                return false;
            endif;
        endif;

        $stmt = $this->prepare( $query );

        if(!$stmt):
            $this->error = $this->conn->error;
            $this->error_list = $this->conn->error_list;
            $this->error_num = $this->conn->errno;
            return false;
        endif;

        $success = $stmt->execute();

        if($success):
            $this->queriesPerformed++;
        endif;

        return $success;
    }
    /**
     * Executes the given query
     *
     * @param string $query query to perform
     * @param array $values values in queries
     * @param string $binds binds of values
     * @param bool $openConnection open the connection if not alive
     *
     * @return MysqlResultCollection|null
     */
    public function fetch(string $query, $values = [], $binds = '', bool $openConnection = true): ? MysqlResultCollection{
        if(!$this->isConnectionOpen()):
            if( $openConnection ):
                $this->openConnection();
            else:
                return null;
            endif;
        endif;
        /**
         * Prepares the query
         */
        $stmt = $this->prepare($query);
        /**
         * Validates if query has no syntax error
         */
        if( !$stmt ):
            $this->error = $this->conn->error;
            $this->error_list = $this->conn->error_list;
            $this->error_num = $this->conn->errno;
            return null;
        endif;
        if( !empty( $values ) && !empty( $binds ) ):
            $stmt->bind_param( $binds, ...$values );
        endif;
        /**
         * Performs the query
         */
        $success = $stmt->execute();
        if(!$success):
            $this->error = $this->conn->error;
            $this->error_list = $this->conn->error_list;
            $this->error_num = $this->conn->errno;
            return null;
        endif;
        $this->queriesPerformed++;
        $result = $stmt->get_result();
        /**
         * Close Statement
         */
        $stmt->close();
        /**
         * Check if valid result
         */
        if(!$result):
            return null;
        endif;
        /**
         * Returns all fetched rows in assoc array
         */
        return $this->fetchResultAsObject($result);
    }
    /**
     * Converts the array to MysqlResultCollection object
     *
     * @param \mysqli_result $result
     * @return MysqlResultCollection
     *
     */
    public function fetchResultAsObject(\mysqli_result $result): MysqlResultCollection{
        $collection = new MysqlResultCollection();
        while ($row = $result->fetch_object()):
            $resultRow = new MysqlResultRow();
            foreach (get_object_vars($row) as $name => $value){
                $resultRow->$name = $value;
            }
            $collection->add($resultRow);
        endwhile;
        return $collection;
    }
    /**
     * Checks if connection to DB is open
     *
     * @return bool
     */
    public function isConnectionOpen(): bool{
        return !empty( $this->conn ) && $this->conn->ping();
    }
    /**
     * Returns the DB connection Object
     *
     * @return \mysqli|null
     */
    public function getConnectionObject(): ?\mysqli{
        return $this->conn;
    }
    /**
     * Returns number of queries performed in current session
     *
     * @return int
     */
    public function getQueriesPerformed(): int{
        return $this->queriesPerformed;
    }
    /**
     * Returns the DB Host
     *
     * @return string
     */
    public function getHost(): string{
        return $this->host;
    }
    /**
     * Returns the DB Username
     *
     * @return string
     */
    public function getUsername(): string{
        return $this->user;
    }
    /**
     * Returns the DB Password
     *
     * @return string
     */
    public function getPassword(): string{
        return $this->password;
    }
    /**
     * Returns the DB Name
     *
     * @return string
     */
    public function getDBName(): string{
        return $this->dbName;
    }
    /**
     * Returns the DB Port
     *
     * @return int
     */
    public function getPort(): int{
        return $this->port;
    }
    /**
     * Returns the DB Socket
     *
     * @return string
     */
    public function getSocket(): string{
        return $this->socket;
    }

    /**
     * Returns the latest error
     *
     * @return string
     */
    public function getError():? string
    {
        return $this->error;
    }

    /**
     * Returns the list of query error
     *
     * @return array
     */
    public function getErrorList():? array
    {
        return $this->error_list;
    }

    /**
     * Returns the recent error number
     *
     * @return int
     */
    public function getErrorNum():? int
    {
        return $this->error_num;
    }

    /**
     * Closes the connection when instance variable is unset or at the end of the script.
     *
     * @since 1.0
     */
    public function __destruct() {
        if( !empty($this->conn) ):
            $this->closeConnection();
        endif;
    }
}
