<?php

namespace Kdyby\Database;


/**
 * Description of Entity
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class Entity extends \Nette\Object
{

	const REVISIONS = TRUE;
	

	/** @var \Kdyby\Database\Repository */
	private $Repository;



	public function __construct(Repository $repository)
	{
		$this->Repository = $repository;
	}


	public function getRepository()
	{
		return $this->Repository;
	}



	/**================= Mapper =================*/



	public function save()
	{
		if ($this->repository === NULL) {
			throw new \InvalidStateException("Repository is required!");
		}

		return $this->repository->save($this);
	}


	abstract function update(array $values);

	abstract function refresh();

	abstract function validate();

	abstract function getPropertiesMap();

	abstract function applyChanges(array $values);

}