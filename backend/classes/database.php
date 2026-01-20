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
            $info = (object)[];
            $info->message = "Database connection error: " . $e->getMessage();
            $info->error = true;
            echo json_encode($info);
            die;
        }
    }

    public function read($query, $data = [])
    {
        $con = $this->connect();
        try{
            $stm = $con->prepare($query);
            $check = $stm->execute($data);

            if ($check) {
                $result = $stm->fetchAll(PDO::FETCH_OBJ);
                if (is_array($result) && count($result) > 0) {
                    return $result;
                }
            }
        }catch(PDOException $e){
             $info = (object)[];
             $info->message = "Database query error: " . $e->getMessage();
             $info->error = true;
             echo json_encode($info);
             die;
        }
        return false;
    }

    public function write($query, $data = [])
    {
        $con = $this->connect();
        try{
            $stm = $con->prepare($query);
            $check = $stm->execute($data);

            if ($check) {
                $id = $con->lastInsertId();
                return $id ? $id : true;
            }
        }catch(PDOException $e){
             $info = (object)[];
             $info->message = "Database write error: " . $e->getMessage();
             $info->error = true;
             echo json_encode($info);
             die;
        }
        return false;
    }
}
