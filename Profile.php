<?php

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
