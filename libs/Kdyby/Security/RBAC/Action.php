<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Security\RBAC;

use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @ORM\Entity
 * @ORM\Table(name="rbac_actions")
 */
class Action extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @ORM\Column(type="string", unique=TRUE) @var string */
	private $name;

	/** @ORM\Column(type="string", nullable=TRUE) @var string */
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
