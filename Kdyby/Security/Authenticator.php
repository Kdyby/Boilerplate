<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */

namespace Kdyby\Security;

use Doctrine;
use Nette;
use Nette\Security\AuthenticationException;
use Kdyby;



/**
 * Users authenticator.
 */
final class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

	/** @var Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var array */
	private $parameters;



	/**
	 * @param Doctrine\ORM\EntityManager $entityManager
	 * @param array $security
	 */
	public function __construct(Doctrine\ORM\EntityManager $entityManager, $parameters)
	{
		$this->entityManager = $entityManager;
		$this->parameters = $parameters;
	}



	/**
	 * Performs an authentication
	 *
	 * @param  array
	 * @return Nette\Security\IIdentity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		throw new Nette\NotImplementedException("Needs refatoring");

		$username = $credentials[self::USERNAME];
		$password = $credentials[self::PASSWORD];

		$identityRepository = $this->entityManager->getRepository($this->parameters['identity.class']);

		if (strpos($username, '@') !== FALSE) {
			$identity = $identityRepository->findOneByEmail($username);
		} else {
			$identity = $identityRepository->findOneByUsername($username);
		}

//		$identity->addRoles(array(
//				new Kdyby\Security\Acl\Role('registered'),
//				new Kdyby\Security\Acl\Role('admin')
//			));

		return $identity;


//		//$row = dibi::fetch('SELECT * FROM users WHERE login=%s', $username);
//
//		if (!$row) {
//			throw new AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
//		}
//
//		if ($row->password !== $password) {
//			throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
//		}
//
//		unset($row->password);
//		return new Identity($row->id, $row->role, $row);
	}

}