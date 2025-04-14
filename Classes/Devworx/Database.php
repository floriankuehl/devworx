<?php

namespace Devworx;

interface IDatabase {
  function conditions(): array;
  
  function connect(): bool;
  function disconnect(): bool;
  function connected(): bool;
  function error(): string;
  function statement(string $query,string $format,array $values);
  function prepare(string $query,string $format,array $values);
  function query(string $query,bool $one,int $mode);
  function insertID(): ?int;
  function escape(string $value): string;
}

class Database implements IDatabase {
  
  const CHARSET = "utf8mb4";
  
  protected 
    $credentials = null,
    $connection = null;
  
  function __construct(
    string $host,
    string $user,
    string $password,
    string $database
  ){
    $this->credentials = [
      $host, 
      $user, 
      $password, 
      $database
    ];
  }
  
  function conditions(): array {
    return [
      "( hidden = 0 )",
      "ISNULL(deleted)",
    ];
  }
  
  function connect(): bool {
    if( isset( $this->connection ) )
      return $this->connection->ping();
    $this->connection = new \mysqli(...$this->credentials);
    $this->connection->set_charset(self::CHARSET);
    return isset($this->connection);
  }
  
  function disconnect(): bool {
    if( isset( $this->connection ) )
      return $this->connection->close();
    return true;
  }
  
  function connected(): bool {
    return isset($this->connection);
  }
  
  function error(): string {
    return $this->connection->error;
  }
  
  function result(string $query){
    return $this->connection->query($query);
  }
  
  function query(string $query,bool $one=false,int $mode=MYSQLI_NUM){
    $result = $this->result($query);
    if( $result === false )
      throw new \Exception($this->error());
    if( $result === true )
      return $result;
    return $one ? 
      $result->fetch_array($mode) : 
      $result->fetch_all($mode);
  }
  
  function statement(string $query,string $format,array $values){
    $connection = $this->connection;
    $stmt = $connection->prepare($query);
    
    $values = array_map(function($value) use ($connection){
      return $connection->real_escape_string($value);
    },$values);
    
    $stmt->bind_param($format, ...$values);
    
    return $stmt;
  }
  
  function prepare(string $query,string $format,array $values){
    $stmt = $this->statement($query,$format,$values);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if( is_bool($result) ) 
      return $result;
    
    $rows = [];
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $rows []= $row;
    }
    
    return $rows;
  }
  
  function insertID(): ?int {
    return intval($this->connection->insert_id);
  }
  
  function escape(string $value): string {
    return $this->connection->real_escape_string($value);
  }
  
  function get(string $table,string $pk,string $uid=null,bool $conditions=true): ?array {
    $one = isset($uid) && !empty($uid);
    $conditions = $conditions ? self::conditions() : [];
    if( $one )
      $conditions []= "( {$pk} = {$uid} )";
    $conditions = implode(" AND ",$conditions);
    return $this->query("SELECT * FROM {$table} WHERE {$conditions};",$one,MYSQLI_ASSOC);
  }
  
  function add(string $table,array $data): int {
    $fields = array_map([$this,'escape'], array_keys($data) );
    $values = array_map( function($v){ return "'{$v}'"; }, array_values($data) );
    $fields = implode(",",$fields);
    $values = implode(",",$values);
    $result = $this->result("INSERT INTO {$table} ({$fields}) VALUES ({$values});");
    return $result == FALSE ? 0 : $this->insertID();
  }
  
  function put(string $table,string $pk,int $uid,array $data): bool {
    $fields = [];
    foreach( $data as $k => $v ){
      $k = $this->escape($k);
      $v = $this->escape($v);
      $fields []= "{$k} = '{$v}'";
    }
    $fields = implode(',',$fields);
    return (bool) $this->result("UPDATE {$table} SET ({$fields}) WHERE ({$pk} = '{$uid}') LIMIT 1;");
  }
  
  function remove(string $table,string $pk,int $uid): bool {
    return (bool) $this->result("DELETE FROM {$table} WHERE ({$pk} = '{$uid}') LIMIT 1;");
  }
  
}