<?php

/**
 * Database class
 * 
 *
 *
 *
 *  
 **/

class Database
{
	private $host = "localhost";
	private $name = "webempleo";
	private $user = "root";
	private $pass = "";
	private $linkId;

	function connect()
	{
		$this->linkId = new mysqli($this->host, $this->user, $this->pass, $this->name);
		if ($this->linkId->connect_error) {
			echo "Error0: " . $this->linkId->connect_error;
			return false;
		}
		$this->linkId->set_charset("utf8");
		return true;
	}

	function getLastID()
	{
		return $this->linkId->insert_id;
	}

	function runSql($query)
	{
		$this->linkId->query($query);
		return $this->linkId->affected_rows;
	}

	function getSql($query, $debug = false)
	{
		if ($debug) echo $query . "<br />";
		$result = $this->linkId->query($query);
		if ($result === false) {
			echo $this->linkId->error;
			return [];
		}
		$res = [];
		while ($row = $result->fetch_assoc()) {
			$res[] = $row;
		}
		$result->free();
		return $res;
	}

	function fstr($str)
	{
		return $this->linkId->real_escape_string(htmlspecialchars(trim($str)));
	}

	function runSelectBare($query, $single = false, $num = false)
	{
		$result = $this->linkId->query($query);
		$res = [];

		if ($single) {
			$row = $result->fetch_row();
			$res = $row[0];
		} else {
			while ($row = $num ? $result->fetch_row() : $result->fetch_assoc()) {
				$res[] = $row;
			}
		}
		$result->free();
		return $res;
	}

	function runSelect($tables, $where = "1", $fieldsA = "*", $order = false, $limit = false, $offset = false, $group = false, $single = false, $num = false, $dep = 0)
	{
		$table = is_array($tables) ? implode(", ", $tables) : $tables;
		if (is_array($fieldsA)) {
			$fields = "";
			foreach ($fieldsA as $key => $val) {
				$fields .= is_string($key) ? "$key AS $val, " : "$val, ";
			}
			$fields = rtrim($fields, ", ");
		} else {
			$fields = $fieldsA;
		}

		$query = "SELECT $fields FROM $table WHERE $where" .
			($group ? " GROUP BY $group" : "") .
			($order ? " ORDER BY $order" : "") .
			($limit ? " LIMIT $limit" : "") .
			($offset ? " OFFSET $offset" : "");

		if ($dep == 1) echo $query . "<br />";

		$result = $this->linkId->query($query);
		if (!$result) return [];

		$res = [];
		if ($single) {
			$row = $result->fetch_row();
			$res = $row[0];
		} else {
			while ($row = $num ? $result->fetch_row() : $result->fetch_assoc()) {
				$res[] = $row;
			}
		}
		$result->free();
		return $res;
	}

	function callproc($query)
	{
		$res = [];
		$this->linkId->multi_query($query);
		do {
			if ($result = $this->linkId->store_result()) {
				while ($row = $result->fetch_assoc()) {
					$res[] = $row;
				}
				$result->free();
			}
		} while ($this->linkId->more_results() && $this->linkId->next_result());
		return $res;
	}

	function runUpdate($table, $valuesA, $where = "1", $ver = 0)
	{
		$set = [];
		foreach ($valuesA as $key => $value) {
			$set[] = "`$key`=" . ($value === NULL || $value === 'NOW()' ? $value : "'" . str_replace("'", "\'", $value) . "'");
		}
		$query = "UPDATE $table SET " . implode(", ", $set) . " WHERE $where";
		if ($ver == 1) echo "$query;<br />";
		$this->linkId->query($query);
		return $this->linkId->affected_rows;
	}

	function runDelete($table, $where = "1")
	{
		$this->linkId->query("DELETE FROM $table WHERE $where");
		return $this->linkId->affected_rows;
	}

	function runInsert($table, $valuesA, $onDuplicate = NULL, $see = 0, $run = true, $cosa = 1)
	{
		$fields = "";
		$values = "";
		foreach ($valuesA as $key => $val) {
			$fields .= "`$key`, ";
			$values .= ($val === NULL || $val === 'NOW()' || $val === 'UUID()') ? "$val, " : "'" . str_replace("'", "\'", $val) . "', ";
		}
		$fields = rtrim($fields, ", ");
		$values = rtrim($values, ", ");

		$onDup = "";
		if ($onDuplicate !== NULL) {
			$onDup = " ON DUPLICATE KEY UPDATE ";
			if (is_array($onDuplicate)) {
				$parts = [];
				foreach ($onDuplicate as $key => $val) {
					$parts[] = "`$key`=" . ($val === NULL ? "NULL" : "'" . str_replace("'", "\'", $val) . "'");
				}
				$onDup .= implode(", ", $parts);
			} else {
				$onDup .= $onDuplicate;
			}
		}

		$query = "INSERT INTO $table ($fields) VALUES ($values)$onDup";
		if ($see == 1) echo $query;
		if ($run) {
			$this->linkId->query($query);
			return $this->linkId->affected_rows;
		} else {
			$var = $cosa == 0 ? "INSERT INTO $table ($fields) VALUES <br />" : "";
			return $var . "($values),<br />";
		}
	}

	function getCells($table)
	{
		$query = "SHOW COLUMNS FROM `$table`";
		return $this->linkId->query($query);
	}

	function translateCellName($cellName)
	{
		$sql = $this->runSelect("mysql_cell_translation", "mysql_name = '" . $this->fstr($cellName) . "'");
		return isset($sql[0]['human_name']) ? $sql[0]['human_name'] : '<span class="faded">[' . $cellName . ']</span>';
	}

	function getError()
	{
		return $this->linkId->error;
	}

	function close()
	{
		$this->linkId->close();
	}

	function mensaje($mensaje, $tipo = "success")
	{
		echo "<br /><div class='$tipo msg'><p>$mensaje</p></div>";
	}

	function logaction($userid, $action)
	{
		$action = $this->fstr($action);
		$data = [
			"user_id" => $userid,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"action" => $action,
			"fecha" => "NOW()"
		];
		$this->runInsert("logs", $data);
		return true;
	}
}
