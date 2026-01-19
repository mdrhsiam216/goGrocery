<?php

class Database
{
	private $host = "localhost";
	private $username = "root";
	private $password = "";
	private $db_name = "goGrocery";

    function connect()
    {
        $string = "mysql:host=$this->host;dbname=$this->db_name";
        try {
            $con = new PDO($string, $this->username, $this->password);
            return $con;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function read($query, $data = [])
    {
        $con = $this->connect();
        $stm = $con->prepare($query);
        $check = $stm->execute($data);

        if ($check) {
            $result = $stm->fetchAll(PDO::FETCH_OBJ);
            if (is_array($result) && count($result) > 0) {
                return $result;
            }
        }
        return false;
    }

    public function write($query, $data = [])
    {
        $con = $this->connect();
        $stm = $con->prepare($query);
        $check = $stm->execute($data);

        if ($check) {
            return $con->lastInsertId();
        }
        return false;
    }
}
