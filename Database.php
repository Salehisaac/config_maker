<?php


namespace DataBase;

use PDO;
use PDOException;

class Database
{
    private $connection;
    // $user->email
    // $user['email'];
    private $option = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');


    private $dbHost = 'localhost';
    private $dbName = 'vpn';
    private $dbUsername = 'root';
    private $dbPassword = '';

    function __construct()
    {
        try {
            $this->connection = new PDO("mysql:host=" . $this->dbHost . ";dbname=" . $this->dbName, $this->dbUsername, $this->dbPassword, $this->option);
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
        $sql = "UPDATE " . $tableName . " SET";
        foreach (array_combine($fields, $values) as $field => $value) {
            if ($value) {
                $sql .= " `" . $field . "` = ? ,";
            } else {
                $sql .= " `" . $field . "` = NULL ,";
            }
        }

        $sql .= " updated_at = now()";
        $sql .= ' WHERE id = ?';

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute(array_merge(array_filter(array_values($values)),  [$id]));
            // [0 => 'hassan', 1 => 30];
            return true;
        } catch (PDOException $e) {
            echo 'error ' . $e->getMessage();
            return false;
        }
    }



    // delete('categories', 2);
    public function delete($tableName, $id)
    {
        $sql = "DELETE FROM " . $tableName . " WHERE id = ? ;";
        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute([$id]);
            return true;
        } catch (PDOException $e) {
            echo 'error ' . $e->getMessage();
            return false;
        }
    }
}
