<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security\RBAC;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @Orm:Entity
 * @Orm:Table(name="rbac_actions")
 */
class Action extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @Orm:Column(type="string", unique=TRUE) @var string */
	private $name;

	/** @Orm:Column(type="string", nullable=TRUE) @var string */
	private $description;



	/**
	 * @param string $name
	 * @param string $description
	 */
	public function __construct($name, $description = NULL)
	{
		if (!is_string($name)) {
			throw new Kdyby\InvalidArgumentException("Given name is not string, " . gettype($name) . " given.");
		}

		if (substr_count($name, Privilege::DELIMITER)) {
			throw new Kdyby\InvalidArgumentException("Given name must not containt " . Privilege::DELIMITER);
		}

		$this->name = $name;
		$this->setDescription($description);
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}



	/**
	 * @param string $description
	 * @return Action
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

}
