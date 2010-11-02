<?php

namespace Kdyby\Database;



/**
 * Description of Datasource
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class DataSource extends ConnectedObject implements IDataSource
{

	/** @var \DibiFluent */
	private $query;

	/** @var \DibiResult */
	private $result;

	/** @var int */
	private $count;

	/** @var int */
	private $totalCount;

	/** @var \Kdyby\Database\EntityBin */
	private $_mapper;



	public function __construct()
	{
		$mapper = $this->reflection->name.'s';

		if (isset($entities[$mapper])) {
			$this->_mapper = $entities[$mapper];
		}
	}



	abstract protected function createQuery();



	public function getQuery()
	{
		if ($this->query === NULL) {
			$this->query = $this->createQuery();
		}

		return $this->query;
	}



	/**
	 * Adds conditions to query.
	 * @param  mixed  conditions
	 * @return DibiDataSource  provides a fluent interface
	 */
	public function where($cond)
	{
		if (is_array($cond)) {
			// TODO: not consistent with select and orderBy
			$this->conds[] = $cond;
		} else {
			$this->conds[] = func_get_args();
		}
		$this->result = $this->count = NULL;
		return $results;
	}



	/**
	 * Selects columns to order by.
	 * @param  string|array  column name or array of column names
	 * @param  string  		 sorting direction
	 * @return DibiDataSource  provides a fluent interface
	 */
	public function orderBy($row, $sorting = 'ASC')
	{
		if (is_array($row)) {
			$this->sorting = $row;
		} else {
			$this->sorting[$row] = $sorting;
		}
		$this->result = NULL;
		return $this;
	}



	/**
	 * Limits number of rows.
	 * @param  int limit
	 * @param  int offset
	 * @return DibiDataSource  provides a fluent interface
	 */
	public function applyLimit($limit, $offset = NULL)
	{
		$this->limit = $limit;
		$this->offset = $offset;
		$this->result = $this->count = NULL;
		return $this;
	}



	/**
	 * Returns the dibi connection.
	 * @return DibiConnection
	 */
	final public function getConnection()
	{
		return $this->connection;
	}


	/********************* for datagrid ****************f*p**/



	/**
	 * Get list of columns available in datasource
	 * @return array
	 */
	public function getColumns()
	{
		
	}

	/**
	 * Does datasource have column of given name?
	 *
	 * @return boolean
	 */
	function hasColumn($name);


	/**
	 * Return distinct values for a selectbox filter
	 * @param string Column name
	 * @return array
	 */
	function getFilterItems($column);



	/********************* executing ****************d*g**/



	/**
	 * Returns (and queries) DibiResult.
	 * @return DibiResult
	 */
	public function getResult()
	{
		if ($this->result === NULL) {
			$this->result = $this->createQuery();
		}
		return $this->result;
	}



	/**
	 * @return DibiResultIterator
	 */
	public function getIterator()
	{
		return $this->getResult()->getIterator();
	}



	/**
	 * Generates, executes SQL query and fetches the single row.
	 * @return DibiRow|FALSE  array on success, FALSE if no next record
	 */
	public function fetch()
	{
		return $this->getResult()->fetch();
	}



	/**
	 * Like fetch(), but returns only first field.
	 * @return mixed  value on success, FALSE if no next record
	 */
	public function fetchSingle()
	{
		return $this->getResult()->fetchSingle();
	}



	/**
	 * Fetches all records from table.
	 * @return array
	 */
	public function fetchAll()
	{
		return $this->getResult()->fetchAll();
	}



	/**
	 * Fetches all records from table and returns associative tree.
	 * @param  string  associative descriptor
	 * @return array
	 */
	public function fetchAssoc($assoc)
	{
		return $this->getResult()->fetchAssoc($assoc);
	}



	/**
	 * Fetches all records from table like $key => $value pairs.
	 * @param  string  associative key
	 * @param  string  value
	 * @return array
	 */
	public function fetchPairs($key = NULL, $value = NULL)
	{
		return $this->getResult()->fetchPairs($key, $value);
	}



	/**
	 * Discards the internal cache.
	 * @return void
	 */
	public function release()
	{
		$this->result = $this->count = $this->totalCount = NULL;
	}



	/********************* counting ****************d*g**/



	/**
	 * Returns the number of rows in a given data source.
	 * @return int
	 */
	public function count()
	{
		if ($this->count === NULL) {
			$this->count = $this->conds || $this->offset || $this->limit
				? (int) $this->connection->nativeQuery(
					'SELECT COUNT(*) FROM (' . $this->__toString() . ') AS t'
				)->fetchSingle()
				: $this->getTotalCount();
		}
		return $this->count;
	}



	/**
	 * Returns the number of rows in a given data source.
	 * @return int
	 */
	public function getTotalCount()
	{
		if ($this->totalCount === NULL) {
			$this->totalCount = (int) $this->connection->nativeQuery(
				'SELECT COUNT(*) FROM ' . $this->sql
			)->fetchSingle();
		}
		return $this->totalCount;
	}

}
