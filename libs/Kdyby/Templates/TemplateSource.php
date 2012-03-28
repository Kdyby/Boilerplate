<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Templates;

use Doctrine;
use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @ORM\Entity()
 * @ORM\Table(name="templates")
 */
class TemplateSource extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	private $name;

	/**
	 * @ORM\Column(type="text", nullable=TRUE)
	 * @var string
	 */
	private $description;

	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	private $source;



	/**
	 * @param string $source
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}



	/**
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}



	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name ?: NULL;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description ?: NULL;
	}



	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}
}
