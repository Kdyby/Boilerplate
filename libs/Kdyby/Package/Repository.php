<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @Entity @Table(name="package_repository")
 */
class Repository extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @var string */
	private $name;

	/** @var string */
	private $path;

	/** @var string */
	private $description;



	/**
	 * @param string $path
	 * @param string $description
	 */
	public function __construct($path, $description = NULL)
	{
		$this->path = $path;
		$this->description = $description;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}



	/**
	 */
	public function fetchPackagesList($stability = 'stable')
	{

	}



	/**
	 */
	public function fetchPackageInfo($packageName, $version = NULL)
	{

	}



	/**
	 */
	public function fetchPackage($packageName, $version = NULL)
	{

	}

}