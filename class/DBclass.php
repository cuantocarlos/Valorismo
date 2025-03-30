<?php

/**
 * Database class
 * 
 *
 *
 *
 *  
 **/

class Database {
	var $host       =       "localhost";
	var $name       =       "webempleo";
	var $user       =       "root";
	var $pass       =       "";
	var $linkId;
	

	
	function getLastID() {
		$id = mysql_fetch_row(mysql_query("SELECT LAST_INSERT_ID()", $this->linkId));
		return $id[0];
	}

	function runSql($query){
		/*Ejecuta SQL y devuelve cantidad de afectados*/
		$q_result = mysql_query($query, $this->linkId);
		return mysql_affected_rows($this->linkId);
	}
	function getSql($query, $debug = false){
		/*Ejecuta SQL y devuelve array*/
		if($debug)
			echo $query."<br />";
		$q_result = mysql_query($query, $this->linkId);
		if($q_result === FALSE) {
			//die(mysql_error()); // TODO: better error handling
			echo mysql_error();
		}
		$res=Array();
				while ($row = mysql_fetch_array($q_result, MYSQL_ASSOC)) {
					array_push($res,$row);
				}
		mysql_free_result($q_result);
		return $res;
	}
	function connect() {

		$this->linkId = mysql_connect($this->host, $this->user, $this->pass);

		if(!$this->linkId) {
			echo "Error0";
			return false;
		}
		if(mysql_select_db($this->name, $this->linkId)) mysql_set_charset('utf8',$this->linkId);return true;
		mysql_close($this->linkId);
		echo "Error1";
		return false;
	}
	function fstr($str){
		$str=mysql_real_escape_string(htmlspecialchars(trim($str)));
		return $str;			
	}
	
	function runSelectBare($query,$single = false,$num=false){
		$q_result = mysql_query($query, $this->linkId);
		
		$res=Array();
		if($single){
			//fetch row
				$res=mysql_fetch_row($q_result);
				$res=$res[0];
				
		}else{
			//fetch array
			if($num){
				while ($row = mysql_fetch_array($q_result, MYSQL_NUM)) {
					array_push($res,$row);
				}
			}else{
				while ($row = mysql_fetch_array($q_result, MYSQL_ASSOC)) {
					array_push($res,$row);
				}
			}

		}
		mysql_free_result($q_result);
		return $res;
	
	}
	function callproc($query){
		$mysqli = new mysqli($this->host, $this->user, $this->pass, $this->name);
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		$mysqli->set_charset("utf8");
		$res=Array();
		if ($result = $mysqli->query($query)) {

			/* fetch object array */
			while($row = $result->fetch_assoc()) {
			  $res[]=$row;
			}

			/* free result set */
			$result->close();
		}

		/* close connection */
		$mysqli->close();
		return $res;
	}
	function runSelect($tables, $where = "1", $fieldsA = "*", $order = false, $limit = false, $offset = false, $group = false, $single = false,$num=false,$dep=0) {
		if(gettype($tables) == "array") {
			$table = "";
			foreach($tables as $t) {
				$table .= $t.", ";
			}
			$table = substr($table, 0, -2);
		} else $table = $tables;
		if(gettype($fieldsA) == "array") {
			$fields = "";
			$keys = array_keys($fieldsA);

			if($keys[0] != '0') {
				foreach($keys as $key) {
					$fields .= $key.' AS '.$fieldsA[$key].', ';
				}
			} else {
				foreach($fieldsA as $field) {
					$fields .= $field.', ';
				}
			}
			$fields = substr($fields, 0, -2);
			
		} else $fields = $fieldsA;
		$query = "SELECT ".$fields." FROM ".$table." WHERE ".$where.
			($group!==false ? " GROUP BY ".$group : "").
			($order!== false?" ORDER BY ".$order : "").
			
			($limit !== false?" LIMIT ".$limit:"").
			($offset !== false?" OFFSET ".$offset:"");
			
		if($dep==1){
			echo $query."<br />";
		}
		
		$q_result = mysql_query($query, $this->linkId);
		
		
		if (!$q_result && 1==2) { // add this check.
			die('Invalid query: ' . mysql_error());
		}
		
		
		$res=Array();
		if($single){
			//fetch row
				$res=mysql_fetch_row($q_result);
				$res=$res[0];
				
		}else{
			//fetch array
			if($num){
				while ($row = mysql_fetch_array($q_result, MYSQL_NUM)) {
					array_push($res,$row);
				}
			}else{
				while ($row = mysql_fetch_array($q_result, MYSQL_ASSOC)) {
					array_push($res,$row);
				}
			}

		}
		mysql_free_result($q_result);
		return $res;
	}
	
function mysqli_clean_connection()
{
while(mysqli_more_results($this->linkId))
{
if(mysqli_next_result($this->linkId))
{
$result = mysqli_use_result($this->linkId);
mysql_free_result($this->linkId);
}
}
} 	
	function runUpdate($table, $valuesA, $where = "1", $ver = 0) {
		if(gettype($valuesA) == "array") {
			$fields = "";
			$values = "";
			$keys = array_keys($valuesA);
			foreach($keys as $key) {
				if($valuesA[$key] !== NULL || $valuesA[$key]==='NOW()')
					$values .= "`".$key."`='".str_replace("'",'\'', $valuesA[$key])."',";
				else
					$values .= $key."=".$valuesA[$key].",";
				
			}
			$fields = substr($fields, 0, -1);
			$values = substr($values, 0, -1);
		} else $values = $valuesA;
		$query = "UPDATE ".$table." SET ".$values." WHERE ".$where;
		if($ver == 1) echo "$query;<br />";
		if(mysql_query($query, 
				$this->linkId))
			return mysql_affected_rows($this->linkId);
		return false;
	}
	
	function runDelete($table, $where = "1") {
		if(mysql_query("DELETE FROM ".$table." WHERE ".$where, $this->linkId))
			return mysql_affected_rows($this->linkId);
		//echo "DELETE FROM ".$table." WHERE ".$where;
		return false;
	}
	
	function runInsert($table, $valuesA, $onDuplicate = NULL, $see = 0, $run = true, $cosa = 1) {
		if(gettype($valuesA) == "array") {
			$fields = "";
			$values = "";
			$keys = array_keys($valuesA);
			foreach($keys as $key) {
				$fields .= "`".$key."`, ";
				
				$values .= ($valuesA[$key]===NULL || $valuesA[$key]==='NOW()' || $valuesA[$key]==='UUID()' ?"".$valuesA[$key].", ":"'".str_replace("'", '\'', $valuesA[$key])."', ");
			}
			$fields = substr($fields, 0, -2);
			$values = substr($values, 0, -2);
		}
		
		$onDup = "";
		if($onDuplicate != NULL) {
			$onDup = " ON DUPLICATE KEY UPDATE ";
			if(gettype($onDuplicate) == "array") {
				$keys = array_keys($onDuplicate);
				foreach($keys as $key) {
					$onDup .= '`'.$key.'`='.($onDuplicate[$key]===NULL?"NULL,":"'".str_replace("'", '\'', $onDuplicate[$key])."', ");
				}
				$onDup = substr($onDup, 0, -2);
			} else $onDup .= $onDuplicate;
		}
		$query = "INSERT INTO ".$table.($fields!==NULL?"(".$fields.")":"").
			" VALUES (".$values.")".$onDup;
		if($see == 1){
			echo $query;
		}
		if($run){
			if(mysql_query($query, $this->linkId)) 
				return mysql_affected_rows($this->linkId);
			return false;
		}else{
			$var = "";
			if($cosa == 0) $var = "INSERT INTO ".$table.($fields!==NULL?"(".$fields.")":"")." VALUES <br />";
			return $var."(".$values."),<br />";
		}
	}
	
	function getCells($table){
		$query = "SHOW COLUMNS FROM `".$table."`";
		$fields = mysql_query($query, $this->linkId) or die('hej');
		return $fields;
	}
	
	function translateCellName($cellName){
		$sql = $this->runSelect("mysql_cell_translation","mysql_name = '".$cellName."'");
		$row = mysql_fetch_assoc($sql);
		return $row['human_name']?$row['human_name']:'<span class="faded">['.$cellName.']</span>';
	}
	
	function getError() {
		return mysql_error($this->linkId);
	}
	
	function close()
	{
		mysql_close($this->linkId);
	}
	function mensaje($mensaje, $tipo = "success"){
		?><br /><div class='<?php echo $tipo; ?> msg'><p><?php echo $mensaje; ?></p></div><?php
	}
	function logaction($userid,$action)
		{
			$action=$this->fstr($action);
			$toins=Array("user_id" => $userid ,"ip" => $_SERVER['REMOTE_ADDR'],"action" => $action,"fecha" => "NOW()");
			$this->runInsert("logs", $toins, NULL);
			
			return true;
			
		}
}
?>