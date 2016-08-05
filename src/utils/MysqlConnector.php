<?php

/**
 * Created by PhpStorm.
 * User: rmukherjee
 * Date: 7/29/2016
 * Time: 12:54 PM
 */
class MysqlConnector
{

    private $_servername;
    private $_username;
    private $_password;
    private $_dbName;

    private function _setMysqlParams($username, $password, $servername, $dbName)
    {
        $this->_password = $password;
        $this->_username = $username;
        $this->_servername = $servername;
        $this->_dbName = $dbName;
    }

    public function __construct($username, $password, $servername, $dbName)
    {
        $this->_setMysqlParams($username, $password, $servername, $dbName);
        echo "The username set is {$this->_username} \n";
    }

    /**
     * @return mysqli
     */
    public function getMysqlConnection()
    {
        $conn = new mysqli($this->_servername, $this->_username, $this->_password, $this->_dbName);
        if ($conn->connect_error) {
            echo("Connection failed: " . $conn->connect_error);
            return null;
        }
        echo "Connected successfully \n";
        return $conn;
    }
}