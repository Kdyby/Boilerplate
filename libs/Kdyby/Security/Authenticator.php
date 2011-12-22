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
use Kdyby\Doctrine\Dao;
use Nette;
use Nette\Security\AuthenticationException;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

	/** @var \Kdyby\Doctrine\Dao */
	private $users;



	/**
	 * @param \Kdyby\Doctrine\Dao $users
	 */
	public function __construct(Dao $users)
	{
		$this->users = $users;
	}



	/**
	 * @param array $credentials
	 * @return Nette\Security\IIdentity
	 */
	public function authenticate(array $credentials)
	{
		$identity = $this->users->fetchOne(new IdentityByNameOrEmailQuery($credentials[self::USERNAME]));

		if (!$identity instanceof Identity) {
			throw new AuthenticationException('User not found', self::IDENTITY_NOT_FOUND);

		} elseif (!$identity->isPasswordValid($credentials[self::PASSWORD])) {
			throw new AuthenticationException('Invalid password', self::INVALID_CREDENTIAL);

		} elseif (!$identity->isApproved()) {
			throw new AuthenticationException('Account is not approved', self::NOT_APPROVED);
		}

		return $identity;
	}

}
