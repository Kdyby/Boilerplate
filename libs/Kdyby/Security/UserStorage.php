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
use Nette\Http\Session;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class UserStorage extends Nette\Http\UserStorage
{


	/** @var \Kdyby\Doctrine\Dao */
	private $users;



	/**
	 * @param \Nette\Http\Session $session
	 * @param \Kdyby\Doctrine\Dao $users
	 */
	public function __construct(Session $session, Dao $users)
	{
		parent::__construct($session);
		$this->users = $users;
	}



	/**
	 * @param bool $need
	 *
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

}
