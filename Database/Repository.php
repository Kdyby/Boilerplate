<?php

namespace Kdyby\Database;



/**
 * Description of EntityRepository
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class Repository extends \Nette\Object implements \ArrayAccess
{
	private $Mapper;

	

	public function __construct(EntityMapper $mapper)
	{
		$this->Mapper = $mapper;
	}

	abstract public function create();

	abstract public function save();

	abstract public function find();

	abstract public function delete();

	abstract public function getDataSource();

	abstract public function walkResults($callback, $where = NULL);
//	{
//		$where = func_get_args();
//		$callback = array_shift($where);
//		$results = array();
//		foreach ($this->mapper->find('%ex', $where) as $id => $row) {
//			$result = $callback->invokeArg($this, $row);
//			if ($result) {
//				$results[$id] = $result;
//			}
//		}
//
//		return $result;
//	}

	final public function getMapper()
	{
		
	}

}