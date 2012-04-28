<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Types;

use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property string $salt
 */
class Password extends Nette\Object
{

	/** @var string */
	const SEPARATOR = '##';

	/** @var string */
	private $value;

	/** @var string */
	private $salt;



	/**
	 * @param string $hash
	 * @param string $salt
	 */
	public function __construct($hash = NULL, $salt = NULL)
	{
		$this->value = $hash;
		$this->salt = $salt;
	}



	/**
	 * @param string $salt
	 */
	public function setSalt($salt)
	{
		$this->salt = $salt;
	}



	/**
	 * @return string
	 */
	public function getSalt()
	{
		return $this->salt;
	}



	/**
	 * @return string
	 */
	public function createSalt()
	{
		return $this->salt = Strings::random(5);
	}



	/**
	 * @return string
	 */
	public function getHash()
	{
		return $this->value;
	}



	/**
	 * @param string $password
	 * @return Password
	 */
	public function setPassword($password, $salt = NULL)
	{
		if ($password === NULL) {
			$this->value = NULL;
			$this->salt = NULL;
			return $this;
		}

		if ($salt !== NULL) {
			$this->salt = $salt;

		} elseif ($this->salt === NULL) {
			$this->salt = $this->createSalt();
		}

		$this->value = $this->hashPassword($password, $this->salt);
		return $this;
	}



	/**
	 * @param string $password
	 * @param string $salt
	 */
	public function isEqual($password, $salt = NULL)
	{
		if ($salt !== NULL) {
			$this->salt = $salt;
		}

		return $this->value === $this->hashPassword($password, $this->salt);
	}



	/**
	 * @param string $password
	 * @param string $salt
	 * @return string
	 */
	protected function hashPassword($password, $salt = NULL)
	{
		return hash('sha512', $salt . self::SEPARATOR . (string)$password);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

}
