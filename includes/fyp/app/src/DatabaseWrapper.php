<?php
/**
 * -- Database Wrapper --
 * Contains a wrapper class for accessing the database
 */

namespace Fyp;
class DatabaseWrapper
{
    private $database_connection;
    private $database_connection_settings;
    private $prepared_statement;
    private $errors;

    public function __construct()
    {
        $this->database_connection = null;
        $this->prepared_statement = null;
        $this->errors = [];
    }

    public function setDatabaseConnectionSettings($connection_settings)
    {
        $this->database_connection_settings = $connection_settings;
    }

    // make database connection
    public function makeDatabaseConnection()
    {
        $pdo_error = false;
        $database_settings = $this->database_connection_settings;

        $dsn = $database_settings['dsn'];
        $username = $database_settings['username'];
        $password = $database_settings['password'];
        $attributes = $database_settings['attributes'];

        try {
            $this->database_connection = new \PDO($dsn, $username, $password, $attributes);
        }
        catch (\PDOException $e)
        {
            $pdo_error = $e->getMessage();
        }
        return $pdo_error;
    }

    // safe query
    public function queryDatabase($query_string, $parameters = null)
    {
        $query_parameters = $parameters;
        $pdo_error = false;
        try
        {
            $this->prepared_statement = $this->database_connection->prepare($query_string);
            $execute_result = $this->prepared_statement->execute($query_parameters);
        }
        catch (\PDOException $e)
        {
            $pdo_error = $e->getMessage();
        }
        return $pdo_error;
    }

    //count rows

    // safe fetch row

    // safe fetch array

}