<?php

namespace Bundle;

use PDO;
use Core\CajoleException;

//TODO: refactor module for MSSQL driver as Linux-friendly (ODBC)
//TODO: emulate prepares setting for MSSQL (if above is unnecessary)
//TODO: resolve charset option issue for MSSQL
//TODO: implement modern authentication methods besides mysql_native_password

class Database {
	
		private $db;
		private $last_query;
		private $db_type;
		private $query;
		private $queryParams;
		
		public function __construct($server, $login, $password, $database, $encoding = "utf8", $db_type = "mysql") {
		
			$this->query = "";
			$this->queryParams = array();
			$this->db_type = $db_type;
			
			switch($this->db_type){
				case "mysql":
					$options = array(
						PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
						PDO::ATTR_EMULATE_PREPARES   => false,
						PDO::MYSQL_ATTR_USE_BUFFERED_QUERY	=> true,
					);
					try {
						$this->db = new PDO(
							"mysql:host=$server;dbname=$database;charset=$encoding", 
							$login, $password, $options);
					} catch (PDOException $e) {
						throw new PDOException($e->getMessage(), (int)$e->getCode());
					}
					break;
				case "mssql":
				try {
					$options = array(
						PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
					);
					$this->db = new PDO(
						"sqlsrv:Server=$server;Database=$database", 
						$login, $password, $options);
					} catch (PDOException $e) {
						throw new PDOException($e->getMessage(), (int)$e->getCode());
					}
					break;
			}
			
		}
		
		public function SetDatabase($database) {
			
			$this->db->query("USE $database");
			
		}
		
		public function Query($query, $params = array()) {
			
			$this->query = $query;
			$this->queryParams = $params;
			$stmt = $this->db->prepare($query);
			$stmt->execute($params);
			return $stmt;
			
		}
		
		public function Result($stmt) {
			
			$result = array();

			while($row = $stmt->fetch()) $result[]=$row;
			
			return $result;
			
		}
		
		public function LastInsertId(){
			return $this->db->lastInsertId();
		}
		
		public function Close() {
			
			$this->db = null;
			
		}
		
		public function __destruct() {
			
			$this->Close();
			
		}
	
}

CajoleException::TraceProcessor(function($trace){
	//Refuse a profiler to show connection credentials
	return preg_replace('/Database\-\>__construct(.*)/', 'Database::[CONSTRUCTOR]', $trace);
});

?>