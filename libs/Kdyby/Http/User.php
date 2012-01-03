<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Http;

use Kdyby;
use Kdyby\Doctrine\Dao;
use Nette;
use Nette\Http;
use Nette\Application\ForbiddenRequestException;
use Nette\Reflection;
use Nette\Security\IAuthorizator;
use Kdyby\Security\Identity;
use Nette\Security\AuthenticationException;
use Kdyby\Security\SerializableIdentity;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 *
 * @method \Kdyby\Security\RBAC\Role[] getRoles() getRoles()
 * @method \Kdyby\Security\Identity getIdentity() getIdentity()
 */
class User extends Nette\Http\User implements Nette\Security\IAuthenticator
{

	/** @var \Kdyby\Doctrine\Dao */
	private $users;



	/**
	 * @param \Nette\Http\Session $session
	 * @param \Nette\DI\IContainer $context
	 * @param \Kdyby\Doctrine\Dao $users
	 */
	public function __construct(Http\Session $session, Nette\DI\IContainer $context, Dao $users)
	{
		parent::__construct($session, $context);
		$this->users = $users;
	}



	/**
	 * @param array $credentials
	 *
	 * @return \Nette\Security\IIdentity
	 */
	public function authenticate(array $credentials)
	{
		$identity = $this->users->fetchOne(new Kdyby\Security\IdentityByNameOrEmailQuery($credentials[self::USERNAME]));

		if (!$identity instanceof Nette\Security\IIdentity) {
			throw new AuthenticationException('User not found', self::IDENTITY_NOT_FOUND);

		} elseif (!$identity->isPasswordValid($credentials[self::PASSWORD])) {
			throw new AuthenticationException('Invalid password', self::INVALID_CREDENTIAL);

		} elseif (!$identity->isApproved()) {
			throw new AuthenticationException('Account is not approved', self::NOT_APPROVED);
		}

		return new SerializableIdentity($identity);
	}



	/**
	 * @todo: validation rules
	 *
	 * @param string $username
	 * @param string $password
	 * @return \Kdyby\Security\Identity
	 */
	public function register($username, $password)
	{
		return $this->users->save(new Identity($username, $password));
	}



	/**
	 * @param bool $need
	 * @return \Nette\Http\SessionSection
	 */
	protected function getSessionSection($need)
	{
		$section = parent::getSessionSection($need);
		if ($section->identity instanceof SerializableIdentity && !$section->identity->isLoaded()) {
			$section->identity->load($this->users);
		}

		return $section;
	}



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
		return $identity ? $identity->getRoleIds() : array($this->authenticatedRole);
	}



	/**
	 * @param string $resource
	 * @param string $privilege
	 * @param string $message
	 *
	 * @throws \Nette\Application\ForbiddenRequestException
	 */
	public function needAllowed($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL, $message = NULL)
	{
		if (!$this->isAllowed($resource, $privilege)) {
			throw new ForbiddenRequestException($message ?: "User is not allowed to " . ($privilege ? $privilege : "access") . " the resource" . ($resource ? " '$resource'" : NULL) . ".");
		}
	}



	/**
	 * @param \Reflector $element
	 * @param string $message
	 *
	 * @throws \Nette\Application\ForbiddenRequestException
	 *
	 * @return bool
	 */
	public function protectElement(\Reflector $element, $message = NULL)
	{
		if (!$element instanceof Reflection\Method && !$element instanceof Reflection\ClassType) {
			return FALSE;
		}

		$user = (array)$element->getAnnotation('User');
		$message = isset($user['message']) ? $user['message'] : $message;
		if (in_array('loggedIn', $user) && !$this->isLoggedIn()) {
			throw new ForbiddenRequestException($message ?: "User " . $this->getIdentity()->getId() . " is not logged in.");

		} elseif (isset($user['role']) && !$this->isInRole($user['role'])) {
			throw new ForbiddenRequestException($message ? : "User " . $this->getIdentity()->getId() . " is not in role '" . $user['role'] . "'.");

		} elseif ($element->getAnnotation('user')) {
			throw new Kdyby\UnexpectedValueException("Annotation 'user' in $element should have been 'User'.");
		}

		$allowed = (array)$element->getAnnotation('Allowed');
		$message = isset($allowed['message']) ? $allowed['message'] : $message;
		if ($allowed) {
			$resource = isset($allowed[0]) ? $allowed[0] : IAuthorizator::ALL;
			$privilege = isset($allowed[1]) ? $allowed[1] : IAuthorizator::ALL;
			$this->needAllowed($resource, $privilege, $message);

		} elseif ($element->getAnnotation('allowed')) {
			throw new Kdyby\UnexpectedValueException("Annotation 'allowed' in $element should have been 'Allowed'.");
		}
	}

}
