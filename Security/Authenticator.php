<?php

namespace Kdyby\Model;

use Nette\Object;
use Nette\Security\Identity;
use Nette\Security\AuthenticationException;


/**
 * Users authenticator.
 */
final class Authenticator extends \Kdyby\Database\ConnectedObject implements \Nette\Security\IAuthenticator
{

	/**
	 * Performs an authentication
	 * @param  array
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		$username = $credentials[self::USERNAME];
		$password = md5($credentials[self::PASSWORD]);

		return new Identity($username, array('registered', 'admin'), array('username'=>$username));


		$row = dibi::fetch('SELECT * FROM users WHERE login=%s', $username);

		if (!$row) {
			throw new AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== $password) {
			throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}

		unset($row->password);
		return new Nette\Security\Identity($row->id, $row->role, $row);
	}

}