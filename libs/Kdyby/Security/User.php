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
use Nette\Security\IAuthenticator;
use Nette\Security\IAuthorizator;
use Nette\Security\IIdentity;



/**
 * User authentication and authorization.
 *
 * @author David Grudl
 * @author Filip Procházka
 *
 * @property-read Nette\Security\IIdentity $identity
 * @property Nette\Security\IAuthenticator $authenticator
 * @property Nette\Security\IAuthorizator $authorizator
 * @property-read int $logoutReason
 * @property-read array $roles
 * @property-read bool $authenticated
 */
class User extends Nette\Object implements Nette\Http\IUser
{
	/** log-out reason {@link User::getLogoutReason()} */
	const MANUAL = 1,
		INACTIVITY = 2,
		BROWSER_CLOSED = 3;

	/** @var string  default role for unauthenticated user */
	public $guestRole = 'guest';

	/** @var string  default role for authenticated user without own identity */
	public $authenticatedRole = 'authenticated';

	/** @var array of function(User $sender); Occurs when the user is successfully logged in */
	public $onLoggedIn = array();

	/** @var array of function(User $sender); Occurs when the user is logged out */
	public $onLoggedOut = array();

	/** @var string */
	private $namespace = '';

	/** @var Nette\Http\SessionSection */
	private $session;

	/** @var Nette\DI\IContainer */
	private $context;



	public function __construct(Nette\DI\IContainer $context)
	{
		$this->context = $context;
	}



	/********************* Authentication ****************d*g**/



	/**
	 * Conducts the authentication process. Parameters are optional.
	 * @param  mixed optional parameter (e.g. username)
	 * @param  mixed optional parameter (e.g. password)
	 * @return void
	 * @throws Nette\Security\AuthenticationException if authentication was not successful
	 */
	public function login($username = NULL, $password = NULL)
	{
		$this->logout(TRUE);
		$credentials = func_get_args();
		$this->setIdentity($this->context->authenticator->authenticate($credentials));
		$this->setAuthenticated(TRUE);
		$this->onLoggedIn($this);
	}



	/**
	 * Logs out the user from the current session.
	 * @param  bool  clear the identity from persistent storage?
	 * @return void
	 */
	final public function logout($clearIdentity = FALSE)
	{
		if ($this->isLoggedIn()) {
			$this->setAuthenticated(FALSE);
			$this->onLoggedOut($this);
		}

		if ($clearIdentity) {
			$this->setIdentity(NULL);
		}
	}



	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	final public function isLoggedIn()
	{
		$session = $this->getSessionSection(FALSE);
		return $session && $session->authenticated;
	}



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
				$respository = $this->context->doctrine->getRepository(get_class($session->identity));
				$session->identity = $respository->find($session->identity->getId());
			}

			return $session->identity;
		}

		return NULL;
	}



	/**
	 * Returns current user ID, if any.
	 * @return mixed
	 */
	public function getId()
	{
		$identity = $this->getIdentity();
		return $identity ? $identity->getId() : NULL;
	}



	/**
	 * Sets authentication handler.
	 * @param  Nette\Security\IAuthenticator
	 * @return User  provides a fluent interface
	 */
	public function setAuthenticator(IAuthenticator $handler)
	{
		$this->context->removeService('authenticator');
		$this->context->authenticator = $handler;
		return $this;
	}



	/**
	 * Returns authentication handler.
	 * @return Nette\Security\IAuthenticator
	 */
	final public function getAuthenticator()
	{
		return $this->context->authenticator;
	}



	/**
	 * Changes namespace; allows more users to share a session.
	 * @param  string
	 * @return User  provides a fluent interface
	 */
	public function setNamespace($namespace)
	{
		if ($this->namespace !== $namespace) {
			$this->namespace = (string) $namespace;
			$this->session = NULL;
		}
		return $this;
	}



	/**
	 * Returns current namespace.
	 * @return string
	 */
	final public function getNamespace()
	{
		return $this->namespace;
	}



	/**
	 * Enables log out after inactivity.
	 * @param  string|int|DateTime number of seconds or timestamp
	 * @param  bool  log out when the browser is closed?
	 * @param  bool  clear the identity from persistent storage?
	 * @return User  provides a fluent interface
	 */
	public function setExpiration($time, $whenBrowserIsClosed = TRUE, $clearIdentity = FALSE)
	{
		$session = $this->getSessionSection(TRUE);
		if ($time) {
			$time = Nette\DateTime::from($time)->format('U');
			$session->expireTime = $time;
			$session->expireDelta = $time - time();

		} else {
			unset($session->expireTime, $session->expireDelta);
		}

		$session->expireIdentity = (bool) $clearIdentity;
		$session->expireBrowser = (bool) $whenBrowserIsClosed;
		$session->browserCheck = TRUE;
		$session->setExpiration(0, 'browserCheck');
		return $this;
	}



	/**
	 * Why was user logged out?
	 * @return int
	 */
	final public function getLogoutReason()
	{
		$session = $this->getSessionSection(FALSE);
		return $session ? $session->reason : NULL;
	}



	/**
	 * Returns and initializes $this->session.
	 * @return SessionSection
	 */
	protected function getSessionSection($need)
	{
		if ($this->session !== NULL) {
			return $this->session;
		}

		if (!$need && !$this->context->session->exists()) {
			return NULL;
		}

		$this->session = $session = $this->context->session->getSection('Nette.Web.User/' . $this->namespace);

		if (!$session->identity instanceof IIdentity || !is_bool($session->authenticated)) {
			$session->remove();
		}

		if ($session->authenticated && $session->expireBrowser && !$session->browserCheck) { // check if browser was closed?
			$session->reason = self::BROWSER_CLOSED;
			$session->authenticated = FALSE;
			$this->onLoggedOut($this);
			if ($session->expireIdentity) {
				unset($session->identity);
			}
		}

		if ($session->authenticated && $session->expireDelta > 0) { // check time expiration
			if ($session->expireTime < time()) {
				$session->reason = self::INACTIVITY;
				$session->authenticated = FALSE;
				$this->onLoggedOut($this);
				if ($session->expireIdentity) {
					unset($session->identity);
				}
			}
			$session->expireTime = time() + $session->expireDelta; // sliding expiration
		}

		if (!$session->authenticated) {
			unset($session->expireTime, $session->expireDelta, $session->expireIdentity,
				$session->expireBrowser, $session->browserCheck, $session->authTime);
		}

		return $this->session;
	}



	/**
	 * Sets the authenticated status of this user.
	 * @param  bool  flag indicating the authenticated status of user
	 * @return User  provides a fluent interface
	 */
	protected function setAuthenticated($state)
	{
		$session = $this->getSessionSection(TRUE);
		$session->authenticated = (bool) $state;

		// Session Fixation defence
		$this->context->session->regenerateId();

		if ($state) {
			$session->reason = NULL;
			$session->authTime = time(); // informative value

		} else {
			$session->reason = self::MANUAL;
			$session->authTime = NULL;
		}
		return $this;
	}



	/**
	 * Sets the user identity.
	 * @param  Nette\Security\IIdentity
	 * @return User  provides a fluent interface
	 */
	protected function setIdentity(IIdentity $identity = NULL)
	{
		$this->getSessionSection(TRUE)->identity = $identity;
		return $this;
	}



	/********************* Authorization ****************d*g**/



	/**
	 * Returns a list of effective roles that a user has been granted.
	 * @return array
	 */
	public function getRoles()
	{
		if (!$this->isLoggedIn()) {
			return array($this->guestRole);
		}

		$identity = $this->getIdentity();
		return $identity ? $identity->getRoles() : array($this->authenticatedRole);
	}



	/**
	 * Is a user in the specified effective role?
	 * @param  string
	 * @return bool
	 */
	final public function isInRole($role)
	{
		return in_array($role, $this->getRoles(), TRUE);
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
		$authorizator = $this->context->authorizator;
		foreach ($this->getRoles() as $role) {
			if ($authorizator->isAllowed($role, $resource, $privilege)) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * Sets authorization handler.
	 * @param  Nette\Security\IAuthorizator
	 * @return User  provides a fluent interface
	 */
	public function setAuthorizator(IAuthorizator $handler)
	{
		$this->context->removeService('authorizator');
		$this->context->authorizator = $handler;
		return $this;
	}



	/**
	 * Returns current authorization handler.
	 * @return Nette\Security\IAuthorizator
	 */
	final public function getAuthorizator()
	{
		return $this->context->authorizator;
	}

}
