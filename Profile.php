<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Social\Facebook;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property \Nette\ArrayHash $details
 */
class Profile extends Nette\Object
{

	/**
	 * @var Facebook
	 */
	private $facebook;

	/**
	 * @var string
	 */
	private $profileId;

	/**
	 * @var \Nette\ArrayHash
	 */
	private $details;



	/**
	 * @param Facebook $facebook
	 * @param string $profileId
	 */
	public function __construct(Facebook $facebook, $profileId)
	{
		$this->facebook = $facebook;
		$this->profileId = $profileId;
	}



	/**
	 * @return string
	 */
	public function getId()
	{
		if ($this->profileId === 'me') {
			return $this->facebook->getUser();
		}

		return $this->profileId;
	}



	/**
	 * @param string $key
	 * @return \Nette\ArrayHash|NULL
	 */
	public function getDetails($key = NULL)
	{
		if ($this->details === NULL) {
			$this->details = $this->facebook->api('/' . $this->profileId);
		}

		if ($key !== NULL) {
			return isset($this->details[$key]) ? $this->details[$key] : NULL;
		}

		return $this->details;
	}


	public function getPicture()
	{

	}


}
