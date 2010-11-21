<?php

namespace Kdyby\Database;


/**
 * Description of Entity
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 *
 * @property-read \Kdyby\Database\Repository $repository
 */
abstract class Entity extends \Nette\Object
{

	const REVISIONS = TRUE;


	/** @var \Kdyby\Database\Repository */
	private $Repository;



	/**
	 * @param \Kdyby\Database\Repository $repository
	 */
	public function __construct(Repository $repository)
	{
		$this->Repository = $repository;
	}


	/**
	 * @return \Kdyby\Database\Repository
	 */
	public function getRepository()
	{
		return $this->Repository;
	}


//     idea: properties private!
//     public function __get()
//     public function __set()
//     všechny properties jako private a přistupovat přes magi




	/**================= Mapper =================*/



	/**
	 * @return \Kdyby\Database\IEntity
	 */
	public function save()
	{
		if ($this->repository === NULL) {
			throw new \InvalidStateException("Repository is required!");
		}

		return $this->repository->save($this);
	}
	
//     abstract function revert();

//     abstract function isValid();

}