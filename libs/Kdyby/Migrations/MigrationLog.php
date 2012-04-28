<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Migrations;

use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @ORM\Entity()
 * @ORM\Table(name="db_migration_log")
 */
class MigrationLog extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/**
	 * @ORM\ManyToOne(targetEntity="PackageVersion", inversedBy="log")
	 * @var \Kdyby\Migrations\PackageVersion
	 */
	private $package;

	/**
	 * @ORM\Column(type="bigint")
	 * @var int
	 */
	private $version;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	private $date;

	/**
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	private $up;



	/**
	 * @param \Kdyby\Migrations\PackageVersion $package
	 * @param \Kdyby\Migrations\Version $version
	 */
	public function __construct(PackageVersion $package, Version $version = NULL)
	{
		$this->package = $package;
		$this->version = $version ? $version->getVersion() : 0;
		$this->date = new \DateTime;
		$this->up = $version !== NULL && $package->getMigrationVersion() < $version->getVersion();
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
