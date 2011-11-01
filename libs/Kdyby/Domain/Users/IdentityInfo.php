<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Domain\Users;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Kdyby\Tools\Mixed;
use Nette;



/**
 * @author Filip Procházka
 *
 * @Entity @Table(name="users_info")
 */
class IdentityInfo extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @var Kdyby\Security\Identity */
	private $identity;

	/** @Column(type="string", nullable=TRUE) */
	private $phone;

	/** @Column(type="array", nullable=TRUE) */
	private $data = array();



	/**
	 * @internal
	 * @param Kdyby\Security\Identity $identity
	 * @return IdentityInfo
	 */
	public function setIdentity(Kdyby\Security\Identity $identity)
	{
		if ($identity->getInfo() !== $this) {
			throw new Nette\InvalidArgumentException("Given identity does not own this info object.");
		}

		$this->identity = $identity;
		return $this;
	}



	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		if (isset($this->{$name})) {
			return $this->{$name};
		}

		return $this->data[$name];
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		if (!Mixed::isSerializable($value)) {
			throw new Nette\NotImplementedException;
		}

		if (isset($this->{$name})) {
			return $this->{$name} = $value;
		}

		$this->data[$name] = $value;
	}



	/**
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		if (isset($this->{$name})) {
			return TRUE;
		}

		return isset($this->data[$name]);
	}



	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		if (isset($this->{$name})) {
			return $this->{$name} = NULL;
		}

		unset($this->data[$name]);
	}

}