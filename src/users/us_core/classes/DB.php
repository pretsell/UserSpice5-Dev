<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
class US_DB {
	private static $_instance = null;
	private $_pdo, $_query, $_error = false, $_results, $_resultsArray, $_count = 0, $_lastId, $_queryCount=0;

	private function __construct($host=null, $dbName=null, $user=null, $passwd=null, $opts=[]) {
        $host   = ($host   ? : configGet('mysql/host'));
        $dbName = ($dbName ? : configGet('mysql/db'));
        $user   = ($user   ? : configGet('mysql/username'));
        $passwd = ($passwd ? : configGet('mysql/password'));
        $opts   = ($opts   ? : configGet('mysql/options'));
        $this->_pdo = $this->getConnection($host, $dbName, $user, $passwd, $opts);
	}

    public static function getConnection($host, $dbName, $user, $passwd, $opts=[]) {
		if (!$opts) {
			$opts = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode = ''");
        }
		try {
			return new PDO('mysql:host='.$host.';dbname='.$dbName, $user, $passwd, $opts);
		} catch(PDOException $e) {
			die($e->getMessage());
		}
    }

	public static function getInstance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new DB();
		}
		return self::$_instance;
	}

	public function query($sql, $params = array()) {
		$this->_queryCount++;
		$this->_error = false;
		if ($this->_query = $this->_pdo->prepare($sql)) {
			$x = 1;
			if (count($params)) {
				foreach ($params as $param) {
					$this->_query->bindValue($x, $param);
					$x++;
				}
			}

			if ($this->_query->execute()) {
				if ($this->_query->columnCount() > 0) {
					$this->_results = $this->_query->fetchALL(PDO::FETCH_OBJ);
                    # Since we rarely use this associative array, calculate it only in results() and first()
					#$this->_resultsArray = json_decode(json_encode($this->_results),true);
				}
				$this->_count = $this->_query->rowCount();
				$this->_lastId = $this->_pdo->lastInsertId();
			} else {
				$this->_error = true;
                $this->_errorInfo = $this->_query->errorInfo();
			}
		}
		return $this;
	}

	public function findAll($table, $orderBy=null) {
		return $this->action('SELECT *',$table, array(), $orderBy);
	}

	public function findById($table,$id) {
		return $this->action('SELECT *',$table,array('id','=',$id));
	}

	public function get($table, $where, $orderBy=null) {
		return $this->action('SELECT *', $table, $where, $orderBy);
	}

	public function delete($table, $where) {
		return $this->action('DELETE', $table, $where);
	}

	public function deleteById($table,$id) {
		return $this->action('DELETE',$table,array('id','=',$id));
	}

	public function action($action, $table, $where=array(), $orderBy=null) {
        global $T;
		$sql = "{$action} FROM ".$T[$table];
		$value = '';
		if (count($where) === 3) {
			$operators = array('=', '>', '<', '>=', '<=');

			$field = $where[0];
			$operator = $where[1];
			$value = $where[2];

			if(in_array($operator, $operators)) {
				$sql .= " WHERE {$field} {$operator} ?";
			}
		}
        if ($orderBy) {
            $sql .= " ORDER BY " . implode(",", (array)$orderBy);
        }
		if (!$this->query($sql, array($value))->error()) {
			return $this;
		}
		return false;
	}

	public function insert($table, $fields = array()) {
        global $T;

		$keys = array_keys($fields);
		$values = null;
		$x = 1;

		foreach ($fields as $field) {
			$values .= "?";
			if ($x < count($fields)) {
				$values .= ', ';
			}
			$x++;
		}

		$sql = "INSERT INTO ".$T[$table]." (`". implode('`,`', $keys)."`) VALUES ({$values})";

		if (!$this->query($sql, $fields)->error()) {
			return true;
		}
		return false;
	}

	public function update($table, $id, $fields) {
        global $T;

		$set = '';
		$x = 1;

		foreach ($fields as $name => $value) {
			$set .= "{$name} = ?";
			if ($x < count($fields)) {
				$set .= ', ';
			}
			$x++;
		}

		$sql = "UPDATE ".$T[$table]." SET {$set} WHERE id = {$id}";

		if (!$this->query($sql, $fields)->error()) {
			return true;
		}
		return false;
	}

	public function results($assoc = false) {
		if ($assoc) {
            return json_decode(json_encode($this->_results),true);
        }
		return $this->_results;
	}

	public function first($assoc = false) {
		if ($this->count() > 0) {
            if ($assoc) {
    			return (array)$this->results()[0];
            } else {
    			return $this->results()[0];
            }
		} else {
			return false;
        }
	}

	public function count() {
		return $this->_count;
	}

	public function errorString() {
        if ($this->_errorInfo) {
    		$rtn = $this->_errorInfo[0].': '.$this->_errorInfo[2].' ('.$this->_errorInfo[1].')';
        } else {
            $rtn = '';
        }
        return $rtn;
	}

	public function error() {
		return $this->_error;
	}

	public function lastId() {
		return $this->_lastId;
	}

	public function getQueryCount() {
		return $this->_queryCount;
	}

	public function getAttribute($attributeValue=null) {
		return $this->_pdo->getAttribute(constant($attributeValue));
	}

	//Add a condition allowing either an individual value or an array
	// arrays will result with "fieldname IN (?,?,?)"
	// values will result with "fieldname = ?"
	// with the $bindvals updated appropriately
	public function calcInOrEqual($fieldnm, $val, &$bindvals) {
		if (!$val) return '';
		$rtn = $fieldnm.' ';
		if (is_array($val)) {
			$rtn .= 'IN ('.str_repeat('?,', count($val) - 1). '?)';
			$bindvals = array_merge($bindvals, $val);
		} else {
			$rtn .= '= ? ';
			$bindvals[] = $val;
		}
		return $rtn;
	}

}
