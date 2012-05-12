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
use Kdyby\Doctrine\Dao;
use Kdyby\Doctrine\Registry;
use Nette;
use Nette\Http\Session;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class UserStorage extends Nette\Http\UserStorage
{

	/**
	 * @var \Kdyby\Doctrine\Registry
	 */
	private $doctrine;



	/**
	 * @param \Nette\Http\Session $session
	 * @param \Kdyby\Doctrine\Registry $doctrine
	 */
	public function __construct(Session $session, Registry $doctrine)
	{
		parent::__construct($session);
		$this->doctrine = $doctrine;
	}



	/**
	 * @param bool $need
	 *
	 * @return \Nette\Http\SessionSection
	 */
	protected function getSessionSection($need)
	{
		/** @var \stdClass|\Nette\Http\SessionSection $section */
		if ($section = parent::getSessionSection($need)) {
			/** @var SerializableIdentity $identity */
			$identity = $section->identity;
			if ($identity instanceof SerializableIdentity && !$identity->isLoaded()) {
				$identity->load($this->doctrine->getDao('Nette\Security\Identity'));
			}
		}

		return $section;
	}

}
