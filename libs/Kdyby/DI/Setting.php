<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 *
 * @Entity @Table(name="settings")
 *
 * @property-read string $name
 * @property-read string $value
 */
class Setting extends Nette\Object
{

	/** @Column(type="string", unique=TRUE) @Id */
	private $name;

	/** @Column(type="string", nullable=TRUE) */
	private $section;

	/** @Column(type="string", nullable=TRUE) */
	private $value;



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __construct($name, $section = NULL, $value = NULL)
	{
		$this->name = $name;
		$this->section = $section;
		$this->value = $value;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}



	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

}