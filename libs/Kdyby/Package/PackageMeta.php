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
 * @todo think of other needed statuses
 *
 * @Orm:Entity
 * @Orm:Table(name="packages")
 */
class PackageMeta extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** Package is ready to be used by application */
	const STATUS_INSTALLED = "installed";

	/** Package files are present in application */
	const STATUS_PRESENT = "present";

	/** Package dependencies are not fulfilled or some files are broken/missing */
	const STATUS_BROKEN = "broken";

	/** @Orm:Column(type="string") @var string */
	private $name;

	/** @Orm:Column(type="string") @var string */
	private $namespace;

	/** @Orm:Column(type="string") @var string */
	private $path;

	/** @Orm:Column(type="string") @var string */
	private $version = 0;

	/** @Orm:Column(type="string") @var string */
	private $status;

	/** @Orm:Column(type="integer") @var integer */
	private $migration;



	/**
	 * @param IPackage $package
	 */
	public function __construct(IPackage $package)
	{
		$this->name = $package->getName();
		$this->namespace = $package->getNamespace();
		$this->path = $package->getPath();
		$this->version = $package->getVersion();
		$this->status = self::STATUS_PRESENT;
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
	public function getNamespace()
	{
		return $this->namespace;
	}



	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->namespace . '\\' . $this->name;
	}



	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}



	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}



	/**
	 * @param string $status
	 */
	public function setStatus($status)
	{
		if (!defined('self::STATUS_' . strtoupper($status))) {
			throw new Kdyby\InvalidArgumentException("Invalida package status given '" . $status . "'.");
		}

		$this->status = strtolower($status);
	}



	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}



	/**
	 * @return boolean
	 */
	public function isPresent()
	{
		return $this->status === self::STATUS_PRESENT;
	}



	/**
	 * @return boolean
	 */
	public function isInstalled()
	{
		return $this->status === self::STATUS_INSTALLED;
	}



	/**
	 * @return boolean
	 */
	public function isBroken()
	{
		return $this->status === self::STATUS_BROKEN;
	}


	/**
	 * @return integer
	 */
	public function getMigration()
	{
		return $this->migration;
	}



	/**
	 * @param integer $migration
	 */
	public function setMigration($migration)
	{
		$this->migration = (int)$migration;
	}



	/**
	 * @return IPackage
	 */
	public function newInstance()
	{
		$class = $this->getClass();
		return new $class();
	}

}
