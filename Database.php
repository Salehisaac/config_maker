<?php


namespace Database;
date_default_timezone_set('Asia/Tehran');
use PDO;
use PDOException;

class Database
{
    private $connection;
    // $user->email
    // $user['email'];
    private $option = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4');


    private $dbHost = 'localhost';
    private $dbName = 'vpn';
    private $dbUsername = 'debian-sys-maint';
    private $dbPassword = 'OYcmiOBeNnrzpopE';

    function __construct()
    {
        try {
            $this->connection = new PDO("mysql:host=" . $this->dbHost . ";dbname=" . $this->dbName , $this->dbUsername, $this->dbPassword, $this->option);

        } catch (PDOException $e) {
            echo 'error ' . $e->getMessage();
        }
    }

    // select('SELECT * FROM categories');
    // select('SELECT * FROM categories WHERE id = ?', [2]);
    public function select($sql, $values = null)
    {

        try {
            $statement = $this->connection->prepare($sql);
            if ($values == null) {
                $statement->execute();
                $result = $statement->fetch(PDO::FETCH_ASSOC);
            }
            else {
                $statement->execute($values);
                $result = $statement->fetch(PDO::FETCH_ASSOC);

            }
            $fianl = $result;
            return $fianl;

        } catch (PDOException $e) {
            echo 'error ' . $e->getMessage();
            return false;
        }
    }

    public function selectAll($sql, $values = null)
    {
        try {
            $statement = $this->connection->prepare($sql);

            if ($values == null) {
                $statement->execute();
            } else {
                $statement->execute($values);
            }

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            return $result;

        } catch (PDOException $e) {
            echo 'error ' . $e->getMessage();
            return false;
        }
    }


    public function fetching($chat_id)
    {

        // Prepare and execute the SQL query with parameters
        $stmt = $this->connection->prepare("SELECT * FROM user_templates WHERE user_id = ?");
        $stmt->execute([$chat_id]);

        // Fetch the result set as an associative array
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $templates;
    }

    public function fetchDefault()
    {

        // Prepare and execute the SQL query with parameters
        $stmt = $this->connection->prepare("SELECT * FROM templates WHERE is_default = ?");
        $stmt->execute([1]);

        // Fetch the result set as an associative array
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $templates;
    }

    public function fetchId($chat_id)
    {
        // Prepare and execute the SQL query with parameters
        $stmt = $this->connection->prepare("SELECT id FROM user_templates WHERE user_id = ?");
        $stmt->execute([$chat_id]);

        // Fetch the result set as an associative array
        $templatesID = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $templatesID;
    }

    public function selectProxiesById($chatId)
    {
        $sql = 'SELECT `proxies` FROM `users` WHERE `id` = ?';
        $values = [$chatId];

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute($values);

            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $proxies = json_decode($result['proxies'], true);
                return $proxies;
            } else {
                return null; // No result found for the given chatId
            }

        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }


    // insert('categories', ['email', 'age'], ['hassan@yahoo.com', 30])
    public function insert($tableName, $fields, $values)
    {
        try {
            $this->connection->query("SET time_zone = '+03:30'");
            $statement = $this->connection->prepare("INSERT INTO " . $tableName . "(" . implode(', ', $fields) . " , created_at) VALUES( :" . implode(', :', $fields) . " , now() );");
            $statement->execute(array_combine($fields, $values));
            // ['email' => 'hassan@yahoo.com', 'age' => 30];
            return true;
        } catch (PDOException $e) {
            echo 'error ' . $e->getMessage();
            return false;
        }
    }


    // update('categories', 2, ['email', 'age'], ['hassan@yahoo.com', 30]);
    public function update($tableName, $id, $fields, $values)
    {
        $sql = "UPDATE " . $tableName . " SET ";
        $setClauses = [];

        foreach (array_combine($fields, $values) as $field => $value) {
            if ($value) {
                $setClauses[] = "`" . $field . "` = ?";
            } else {
                $setClauses[] = "`" . $field . "` = NULL";
            }
        }

        $sql .= implode(", ", $setClauses);
        $sql .= ", updated_at = NOW() WHERE id = ?";

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute(array_merge(array_filter(array_values($values)), [$id]));
            return true;
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }




    // delete('categories', 2);
    public function deleteConfig($tableName, $username , $panel_id)
    {
        $sql = "DELETE FROM " . $tableName . " WHERE name = ? AND panel_id = ?;";
        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute([$username , $panel_id ]);
            return true;
        } catch (PDOException $e) {
            echo 'error ' . $e->getMessage();
            return false;
        }
    }

    public function join($selectColumns, $leftTable, $rightTable, $joinCondition, $limit)
    {


        // Construct the SQL query with the dynamic join condition
        $joinQuery = "
            SELECT {$selectColumns}
            FROM {$leftTable}
            RIGHT JOIN {$rightTable} ON {$joinCondition}
            WHERE {$limit}
            ";

        // Prepare and execute the query
        $stmt = $this->connection->prepare($joinQuery);
        $stmt->execute();

        // Fetch and return the results
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteMessage($tableName, $username)
    {
        $sql = "DELETE FROM " . $tableName . " WHERE id = ?;";
        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute([$username]);
            return true;
        } catch (PDOException $e) {
            echo 'error ' . $e->getMessage();
            return false;
        }
    }




}
