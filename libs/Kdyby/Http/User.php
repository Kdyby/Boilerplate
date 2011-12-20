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
use Nette;
use Nette\Http;
use Nette\Application\ForbiddenRequestException;
use Nette\Reflection;
use Nette\Security\IAuthorizator;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class User extends Nette\Http\User
{

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
