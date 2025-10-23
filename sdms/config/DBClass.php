<?php

class DBClass{
    private $dbh;
    private $stmt;
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $dbname = 'students_data';

    private $options = [

    ];

    function __construct()
    {
        try {
            $this->dbh = new PDO('mysql:host='.$this->host.';dbname='.$this->dbname.';',$this->user,$this->password);  
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
      
    }

    public function connect(){
        return $this->dbh;
    }

    public function query($sql, $data){
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($data);
        return $stmt;
    }

    public function insert(string $table, array $data) {
        $keys = array_keys($data);
        $columns = implode(',', $keys);
        $placeholders = ':' . implode(', :', $keys);
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);
        //return $this->dbh->lastInsertId();
        return $sql;
    }

    public function read($table){
        $sql = "SELECT * FROM $table";
        $stmt = $this->query($sql,[]);
        return $stmt->fetchAll();
    }

    public function readone($table, $conditions) {
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereClauses[] = "$column = :$column";
            $params[":$column"] = $value;
        }
        
        $sql = "SELECT * FROM $table";
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        $sql .= " LIMIT 1";
        
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

  

    public function update(string $table, array $data, string $where = '', array $params = []): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException('No data provided for update');
        }
        // Prepare SET part
        $set = implode(', ', array_map(
            fn($k) => "`$k` = :set_$k", 
            array_keys($data)
        ));

        // Prepare parameters
        $setParams = [];
        foreach ($data as $k => $v) {
            $setParams[":set_$k"] = $v;
        }

        // Build query
        $sql = "UPDATE $table SET $set";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }

        // Execute
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute(array_merge($setParams, $params));
        
        return $stmt->rowCount();
    }

    public function delete($table, $where, $params) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $this->stmt = $this->dbh->prepare($sql);
        return $this->stmt->execute($params);
    }


}

$dbclass = new DBClass();
$conn = $dbclass->connect();

