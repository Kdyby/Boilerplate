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
use Nette\Security\IIdentity;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SimpleAuthenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

	/** @var \Nette\Security\IIdentity */
	private $identity;



	/**
	 * @param \Nette\Security\IIdentity $identity
	 */
	public function __construct(IIdentity $identity)
	{
		$this->identity = $identity;
	}



	/**
	 * @param array $credentials
	 * @return \Nette\Security\IIdentity
	 */
	public function authenticate(array $credentials)
	{
		return $this->identity;
	}


}
