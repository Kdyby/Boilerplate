<?php

namespace Kdyby\Entity;

use Nette;
use Nette\Security\IIdentity;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class User extends Kdyby\Entity\Person implements IIdentity
{

	private $id;

	private $username;

	private $passwordHash;

	private $registeredAt;


	public function getId()
	{
		return $this->id;
	}

	public function getRoles()
	{
		return array();
	}

}