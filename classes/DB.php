<?php


class DB {

    private $db;
    private $error = false;

    function __construct($table, $user, $pass, $host = 'localhost', $charset = 'utf8') {
        try {
            $db = new PDO('mysql:host=' . $host . ';dbname=' . $table, $user, $pass);
        } catch (PDOException $e) {
            die('Error: ' . $e->getMessage());
        }
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $this->db = $db;
        $this->query('set names ?', [$charset]);
    }

    private function prepare($sql, $params) {
        $sql = $this->db->prepare($sql);
        $count = 1;
        if (sizeof($params)) {
            foreach ($params as $item) {
                $sql->bindValue($count, $item, PDO::PARAM_STR);
                $count++;
            }
        }

        return $sql;
    }

    function error() {
        return $this->error;
    }

    function query($sql, array $params = []) {
        $sql = $this->prepare($sql, $params);
        $exec = $sql->execute();
        if (!$exec) {
            $this->error = $sql->errorInfo();
        }

        return $sql;
    }

    function getAll($sql, array $params = []) {
        $sql = $this->prepare($sql, $params);
        $exec = $sql->execute();
        if (!$exec) {
            $this->error = $sql->errorInfo();
        }

        return ($sql ? $sql->fetchAll(PDO::FETCH_ASSOC) : false);
    }

    function getOne($sql, array $params = []) {
        $sql = $this->prepare($sql, $params);
        $exec = $sql->execute();
        if (!$exec) {
            $this->error = $sql->errorInfo();
        }
        if (!$sql) {
            return false;
        }
        $sql = $sql->fetch(PDO::FETCH_ASSOC);

        return array_shift($sql);
    }
}

