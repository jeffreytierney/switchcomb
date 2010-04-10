<?php

class SCDB {

	private $dbserver = SC_DBSERVER;
	private $schema = SC_DBSCHEMA;
	private $dbuser = SC_DBUSER;
	private $dbpwd = SC_DBPWD;
	
	private $last_result;
	
	public $conn;
	function __construct() {
		global $from_email;

		$this->conn = mysql_connect($this->dbserver,$this->dbuser,$this->dbpwd);
		mysql_select_db($this->schema);
	}
	
	function __destruct() {
		@mysql_close($this->conn);
	}
	
	public function query($sql = false) {
		if($sql) {
			$this->last_result = mysql_query($sql);
			return $this->last_result;
		}
	}
	
	public function queryArray($sql = false) {
		if($sql) {
			$this->last_result = @mysql_query($sql);
			$results_array = array();
			while($row = @mysql_fetch_assoc($this->last_result)) {
				$results_array[] = $row;
			}
			return $results_array;
		}
	}
  
  public function q($fields, $tables, $conditions=false, $suffixes=false) {
  
    $sql = "SELECT " . implode(", ", $fields) . " FROM " . implode(", ", $tables);
    if($conditions) {
      $sql .= " WHERE "; 
      $conditions_array = array();
      foreach($conditions as $field=>$value) {
        if(is_array($value)) {
          $conditions_array[] =  $field." IN (".implode(",", $value).")";
        }
        else {
          $conditions_array[] = $field."=".$value;
        }
      }
      $sql .= implode(" AND ", $conditions_array);
    }
    if($suffixes) $sql .= " " .implode(" ", $suffixes);
    
    //echo($sql);
    
    return $this->queryArray($sql);
    
  }
  
  public function insertFromArray($db_array, $table) {
    $sql = "INSERT INTO $table (";
    $fields_array = array();
    $values_array = array();
    foreach($db_array as $key=>$value) {
      $fields_array[] = $key;
      $values_array[] = ($value===null ? "null" : $value);
    }
    $sql .= implode(", ", $fields_array);
    $sql .= ") VALUES(";
    $sql .= implode(", ", $values_array);
    $sql .= ")";
    
    //echo $sql."<br/><br/>";
    
    $this->query($sql);
  }
  
  public function updateFromArray($db_array, $table, $where) {
    $sql = "UPDATE $table SET ";
    $sql_array = array();
    foreach($db_array as $key=>$value) {
      $sql_array[] =  $key."=".($value===null ? "null" : $value);;
    }
    $sql .= implode(", ", $sql_array);
    $sql .= " " . $where;
    //$sql .= " WHERE listing_id=$listing_id";
    //echo $sql."<br/><br/>";
    $this->query($sql);
  }


}

?>
