<?php

namespace Kdyby;

use Nette;
use Kdyby;
use Kdyby\ORM\Mapping\MySQLMapper;


/**
 * Description of User
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class UserMySQLMapper extends MySQLMapper
{

	protected function createEntityMap()
	{
		$map = new Entity\PersonMap('Kdyby\Entity\User', $session);

		$map->addProperty('id');
		$map->addProperty('username');
		$map->addProperty('passwordHash');
		$map->addDateTime('registeredAt');

		return $map;
	}

}