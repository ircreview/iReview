<?php
/* 
 *   iReview CMS
 *     by IRCReview Staff
 *
 *   See CREDITS for more information on developers
 *
 *   @version      0.1.0.0
 *   @copyright    Copyright © 2011 IRCReview
 *   @description  Index file that deals with the
 *                 query string and includes the 
 *                 required files for the end-user. 
 *   @url          http://ircreview.com/
 *
 */
 
class MySQL {
	private $connection;
	private $query_id = -1;
	private $affected_rows = -1;
	var $prefix = '';
	private $last_id = -1;
	private $Language;
	private $Error = false;
	
	function __construct($settings) {
		if(empty($settings['mysql'])) {			
			// Failed to connect to MySQL
			// TODO: Add error pages
			$this->Error = true;
			die("Couldn't find MySQL settings.");
		}
		
		$this->prefix = $settings['mysql']['prefix'];

		$settings['mysql']['host'] .= ':' . $settings['mysql']['port'];
		
		// Let's initiate the MySQL connection.
		if($settings['mysql']['mysql_persistent'] && ini_get("mysql.allow_persistent"))
			$this->connection = @mysql_pconnect($settings['mysql']['host'], $settings['mysql']['user'], $settings['mysql']['pass']);
		else
			$this->connection = @mysql_connect($settings['mysql']['host'], $settings['mysql']['user'], $settings['mysql']['pass']);
			
		if(!$this->connection) {
			// Failed to connect to MySQL
			// TODO: Add error pages
			$this->Error = true;
			die("Connection with MySQL failed: <br />" . mysql_error());
		}
		else {
			// Connection was done and is now active.
		}
		mysql_select_db($settings['mysql']['db'], $this->connection);
	}
	
	function __destruct() {
		if($this->Error != true)
			$this->Close();
	}
	
	function Close() {
		mysql_close($this->connection);
	}
	
	function Escape($sql) {
		if(get_magic_quotes_runtime()) $sql = stripslashes($sql);
   		return @mysql_real_escape_string($sql,$this->connection); 
	}
	
	function createTable($name, $data) { }
	
	function Query($sql) {
		$this->query_id = mysql_query($sql, $this->connection);
		
		if(!$this->query_id) {
			// Failed to execute query.
			// TODO: Add error page or handler in this case (maybe)
			die("MySQL execution failed at '$sql' --> " . mysql_error($this->connection));
		}
		
		$this->last_id = mysql_insert_id($this->connection);
		$this->affected_rows = mysql_affected_rows($this->connection);
		
		return $this->query_id;
	}
	
	function fetchArray($query_id=-1) {
		if ($query_id != -1) {
			$this->query_id = $query_id;
		}
	
		if (isset($this->query_id)) {
			$record = @mysql_fetch_assoc($this->query_id);
		}
	
		return $record;
	}
	
	function getArray($query_id=-1) {
		$out = array();
	
		while ($row = $this->fetchArray($query_id)) {
			$out[] = $row;
		}
	
		$this->freeResult($query_id);
		return $out;
	}
	
	function freeResult($query_id=-1) {
		if ($query_id != -1) {
			$this->query_id = $query_id;
		}
		
		return mysql_free_result($this->query_id);
	}
	
	function queryFirst($sql) {
		$query_id = $this->Query($sql);
		$out = $this->fetchArray($query_id);
		$this->freeResult($query_id);
		return $out;
	}
	
	/* Example: */
	// $MySQL->Insert("users", array('nick' => 'Francis', 'position' => 'developer'));
	function Insert($table, $data) {
		$q="INSERT INTO `".$this->prefix.$table."` ";
   		$v=''; 
		$n='';

		foreach($data as $key=>$val) {
			$n.="`$key`, ";
			if(strtolower($val)=='null') $v.="NULL, ";
			elseif(strtolower($val)=='now()') $v.="NOW(), ";
			else $v.= "'".$this->Escape($val)."', ";
		}
	
		$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";
	
		if($this->Query($q)){
			return mysql_insert_id($this->connection);
		}
		else return false; 
	}
	
	function Select($table, $where, $what='*', $orderby='', $order='ASC') {
		if($orderby != null) {
			$orderby = " ORDER BY `" . $orderby . "` " . $order;
		}
		$q="SELECT ". $what . " FROM `".$this->prefix.$table."` WHERE ";

		foreach($where as $key=>$val) {
			if(strtolower($val)=='null') $q.= "`$key` = NULL, ";
			elseif(strtolower($val)=='now()') $q.= "`$key` = NOW(), ";
			elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $q.= "`$key` = `$key` + $m[1], "; 
			else $q.= "`$key`='".$this->Escape($val)."', ";
		}
	
		$q = rtrim($q, ', ') . $orderby . ';';
	
		return $this->Query($q); 
	}
	
	function SelectAll($table, $what='*', $orderby='', $order='ASC') {
		if($orderby != null) {
			$orderby = " ORDER BY `" . $orderby . "` " . $order;
		}
		$q="SELECT ". $what . " FROM `".$this->prefix.$table."` WHERE 1" . $orderby . ";";
	
		return $this->Query($q); 
	}
	
	/* Example: */
	// $MySQL->Update("users", array('nick' => 'Francismori7', 'position' => 'pwner'), array('nick' => 'Francis'));
	function Update($table, $data, $where) {
		$q="UPDATE `".$this->prefix.$table."` SET ";
		$w="";

		foreach($data as $key=>$val) {
			if(strtolower($val)=='null') $q.= "`$key` = NULL, ";
			elseif(strtolower($val)=='now()') $q.= "`$key` = NOW(), ";
			elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $q.= "`$key` = `$key` + $m[1], "; 
			else $q.= "`$key`='".$this->Escape($val)."', ";
		}
		foreach($where as $key=>$val) {
			if(strtolower($val)=='null') $w.= "`$key` = NULL, ";
			elseif(strtolower($val)=='now()') $w.= "`$key` = NOW(), ";
			elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $w.= "`$key` = `$key` + $m[1], "; 
			else $w.= "`$key`='".$this->Escape($val)."', ";
		}
	
		$q = rtrim($q, ', ') . ' WHERE '.rtrim($w, ', ').';';
	
		return $this->Query($q); 
	}
	
	/* Example: */
	// $MySQL->Delete("users", array('nick' => 'Francismori7', 'position' => 'pwner')); // Deletes all entries from users with Francismori7 as nick and pwner as position.
	function Delete($table, $where) {
		$q="DELETE FROM `".$this->prefix.$table."` WHERE ";
	
		foreach($where as $key => $val) {
			if(strtolower($val)=='null') $q .= '`' . $key . "` = NULL, ";
			elseif(strtolower($val)=='now()') $q .= '`' . $key . "` = NOW(), ";
			elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $q .= "`$key` = `$key` + $m[1], "; 
			else $q .= "`$key`='" . $this->Escape($val) . "', ";
		}
	
		$q = rtrim($q, ', ') . ";";
	
		return $this->Query($q);
	}
	
	function lastInsertedID() {
		return $this->last_id;
	}
	
	function affectedRows() {
		return $this->affected_rows;
	}
	
	function tableprefix($table='') {
		return $this->prefix . $table;
	}
}

?>