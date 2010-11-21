<?php

namespace Kdyby\Database;

use Nette;
use Kdyby;



/**
 * Description of Repository
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class Repository extends Nette\Object implements \ArrayAccess
{

	/** @var \Kdyby\Database\DtM */
	private $DtM;

	/** @var array */
	private $entities = array();



	/**
	 * @param \Kdyby\Database\DtM $DtM
	 */
	public function __construct(DtM $DtM)
	{
		$this->DtM = $DtM;
	}



	/**
	 * @return \Kdyby\Database\DtM
	 */
	final public function getDtM()
	{
		return $this->DtM;
	}



	/********************* interface \ArrayAccess *********************/



	public function offsetSet($id, $entity)
	{
		if ($entity->id == $id && $entity instanceof IEntity) {
			$this->entities[(int)$id] = $entity;
		}
	}



	public function offsetGet($id)
	{
		if (!isset($this->entities[(int)$id])) {
			$this->entities[(int)$id] = $this->getById($id);
		}

		return $this->entities[(int)$id];
	}



	public function offsetExists($id)
	{
		return ($this[$id] !== NULL);
	}



	public function offsetUnset($id)
	{
		$this->delete($this[$id]);
		unset($this->entities[(int)$id]);
	}

}