<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Security;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SimpleUserStorage extends Nette\Object implements Nette\Security\IUserStorage
{

	/** @var bool */
	private $autheticated = FALSE;

	/** @var \Nette\Security\IIdentity */
	private $identity;

	/** @var string */
	private $namespace;



	/**
	 * Sets the authenticated status of this user.
	 * @param bool $state
	 *
	 * @return \Kdyby\Security\SimpleUserStorage
	 */
	public function setAuthenticated($state)
	{
		$this->autheticated = (bool)$state;
		return $this;
	}



	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	public function isAuthenticated()
	{
		return $this->autheticated;
	}



	/**
	 * Sets the user identity.
	 *
	 * @param \Nette\Security\IIdentity|NULL $identity
	 *
	 * @return \Kdyby\Security\SimpleUserStorage
	 */
	public function setIdentity(Nette\Security\IIdentity $identity = NULL)
	{
		$this->identity = $identity;
		return $this;
	}



	/**
	 * Returns current user identity, if any.
	 * @return \Nette\Security\IIdentity|NULL
	 */
	public function getIdentity()
	{
		return $this->identity;
	}



	/**
	 * Changes namespace; allows more users to share a session.
	 * @param string $namespace
	 *
	 * @return \Kdyby\Security\SimpleUserStorage
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = (string)$namespace;
		return $this;
	}



	/**
	 * Returns current namespace.
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}



	/**
	 * Enables log out after inactivity.
	 *
	 * @param int $time
	 * @param int $flags
	 *
	 * @return \Kdyby\Security\SimpleUserStorage
	 */
	public function setExpiration($time, $flags = 0)
	{
		trigger_error(get_called_class() . "::setExpiration() is not supported", E_USER_NOTICE);
		return $this;
	}



	/**
	 * Why was user logged out?
	 * @return NULL
	 */
	public function getLogoutReason()
	{
		return NULL;
	}

}
