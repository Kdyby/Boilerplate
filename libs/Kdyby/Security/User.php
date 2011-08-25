<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security;

use Kdyby;
use Nette;
use Nette\Security\IAuthorizator;



/**
 * @author Filip Procházka
 */
class User extends NetteUser
{

	/**
	 * Returns current user identity, if any.
	 * @return Nette\Security\IIdentity
	 */
	public function getIdentity()
	{
		$session = $this->getSessionSection(FALSE);
		if ($session && $session->identity && $session->identity instanceof Identity) {
			if (!$session->identity->isLoaded()) {
				// TODO: super lazy, EntityManager::merge etc
				$respository = $this->getContext()->sqldb->getRepository(get_class($session->identity));
				$session->identity = $respository->find($session->identity->getId());
			}

			return $session->identity;
		}

		return NULL;
	}



	/**
	 * Has a user effective access to the Resource?
	 * If $resource is NULL, then the query applies to all resources.
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool
	 */
	public function isAllowed($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL)
	{
		$authorizator = $this->getContext()->authorizator;
		foreach ($this->getRoles() as $role) {
			if ($authorizator->isAllowed($role, $resource, $privilege)) {
				return TRUE;
			}
		}

		return $authorizator->isAllowed($this->getIdentity(), $resource, $privilege);
	}

}
