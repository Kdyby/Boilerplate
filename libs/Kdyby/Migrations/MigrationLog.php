<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @Orm:Entity()
 * @Orm:Table(name="db_migration_log")
 */
class MigrationLog extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/**
	 * @Orm:ManyToOne(targetEntity="PackageVersion", inversedBy="log")
	 * @var \Kdyby\Migrations\PackageVersion
	 */
	private $package;

	/**
	 * @Orm:Column(type="integer")
	 * @var int
	 */
	private $version;

	/**
	 * @Orm:Column(type="datetime")
	 * @var \DateTime
	 */
	private $date;

	/**
	 * @Orm:Column(type="boolean")
	 * @var boolean
	 */
	private $up;



	/**
	 * @param \Kdyby\Migrations\PackageVersion $package
	 * @param \Kdyby\Migrations\Version $version
	 */
	public function __construct(PackageVersion $package, Version $version)
	{
		$this->package = $package;
		$this->version = $version->getVersion();
		$this->date = new \DateTime;
		$this->up = $package->getMigrationVersion() < $version->getVersion();
	}



	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}



	/**
	 * @return \Kdyby\Migrations\PackageVersion
	 */
	public function getPackage()
	{
		return $this->package;
	}



	/**
	 * @return int
	 */
	public function getVersion()
	{
		return $this->version;
	}



	/**
	 * @return boolean
	 */
	public function isUp()
	{
		return $this->up;
	}

}
