<?php

require_once 'lib/core/lib/TikiDb.php';

class TikiDb_Pdo_Result
{
	var $result;
	var $numrows;

	function __construct ($result) {
		$this->result = &$result;
		$this->numrows = count ($this->result);
	}

	function fetchRow() {
		return array_shift($this->result);
	}

	function numRows() {
		return $this->numrows;
	}
}

class TikiDb_Pdo extends TikiDb {
	private $db;

	function __construct( $db ) // {{{
	{
		if (!$db) {
			die ("Invalid db object passed to TikiDB constructor");
		}

		$this->db=$db;
		$this->setServerType( $db->getAttribute(PDO::ATTR_DRIVER_NAME) );
	} // }}}

	function qstr( $str ) // {{{
	{
		return $this->db->quote($str);
	} // }}}

	private function _query( $query, $values = null, $numrows = -1, $offset = -1 ) // {{{
	{
		global $num_queries;
		$num_queries++;

		$numrows = intval($numrows);
		$offset = intval($offset);
		if ( $query == null ) {
			$query = $this->getQuery();
		}

		$this->convertQuery( $query );
		$this->convertQueryTablePrefixes( $query );

		if( $offset != -1 && $numrows != -1 )
			$query .= " LIMIT $offset,$numrows";
		elseif( $numrows != -1 )
			$query .= " LIMIT $numrows";

		$starttime=$this->startTimer();

		$pq = $this->db->prepare($query);

		if ($values and !is_array($values)) {
			$values = array($values);
		}
		if ($values) {
			$count = 1;
			foreach($values as $value) {
				$pq->bindValue($count++,$value,is_int($value)?(PDO::PARAM_INT):(PDO::PARAM_STR)) ;
			}
		}

		$result = $pq->execute();

		$this->stopTimer($starttime);

		if (!$result) {
			$tmp = $pq->errorInfo();
			$this->setErrorMessage( $tmp[2] );
			$pq->closeCursor();
			return false;
		} else {
			$this->setErrorMessage( "" );
			if( $pq->columnCount() ) {
				$tmp = new TikiDb_Pdo_Result($pq->fetchAll());
			} else {
				$tmp = new TikiDb_Pdo_Result(array());
			}
			$pq->closeCursor();
			return $tmp;
		}
	} // }}}

	function query($query = null, $values = null, $numrows = -1, $offset = -1, $reporterrors = true ) // {{{
	{
		$result = $this->_query($query,$values, $numrows, $offset);
		if (!$result ) {
			if ($reporterrors) {
				$this->handleQueryError($query, $values, $result);
			}
		}

		return $result;
	} // }}}
}

?>
