<?php

namespace Kdyby\Entity;

use Nette;
use Kdyby;
use Kdyby\Database\IRepository;
use Kdyby\Database\IEntity;




/**
 * Description of User
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class User extends Nette\Web\User implements IEntity
{

	/** @var IRepository */
	private $repository;



	/************** Kdyby\Database\IEntity **************/

	/**
	 * @param IRepository $repository 
	 */
	public function __construct(IRepository $repository)
	{
		$this->repository = $repository;
	}



	/**
	 * @return IRepository
	 */
	public function getRepository()
	{
		return $this->repository;
	}



	/**
	 * @return IEntity
	 */
	public function save()
	{
		if ($this->repository === NULL) {
			throw new \InvalidStateException("Repository is required!");
		}

		return $this->repository->save($this);
	}

	
}
