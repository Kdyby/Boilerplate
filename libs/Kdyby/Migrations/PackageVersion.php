<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Migrations;

use Doctrine;
use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @ORM\Entity()
 * @ORM\Table(name="db_packages", uniqueConstraints={
 * 	@ORM\UniqueConstraint(columns={"name"})
 * })
 */
class PackageVersion extends Kdyby\Doctrine\Entities\IdentifiedEntity
{
	const STATUS_PRESENT = 'present';
	const STATUS_INSTALLED = 'installed';

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $name;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $className;

	/**
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 * @var \Datetime
	 */
	private $migrationVersion;

	/**
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 * @var \DateTime
	 */
	private $lastUpdate;

	/**
	 * @ORM\OneToMany(targetEntity="MigrationLog", mappedBy="package", cascade={"persist"})
	 * @var \Kdyby\Migrations\MigrationLog[]
	 */
	private $log;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $status = self::STATUS_PRESENT;



	/**
	 * @param \Kdyby\Packages\Package $package
	 */
	public function __construct(Kdyby\Packages\Package $package)
	{
		$this->name = $package->getName();
		$this->className = get_class($package);
		$this->lastUpdate = new \DateTime;
		$this->log = new Doctrine\Common\Collections\ArrayCollection();
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
	public function getClassName()
	{
		return $this->className;
	}



	/**
	 * @return VersionDatetime
	 */
	public function getMigrationVersion()
	{
		return $this->migrationVersion ? clone $this->migrationVersion : NULL;
	}



	/**
	 * @return \DateTime
	 */
	public function getLastUpdate()
	{
		return $this->lastUpdate;
	}



	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}



	/**
	 * @param string $status
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 */
	public function setStatus($status)
	{
		$constant = 'static::STATUS_' . strtoupper($status);
		if (!defined($constant)) {
			throw new Kdyby\InvalidArgumentException('Invalid PackageVersion status "' . $status . '" was given.');
		}

		$this->status = constant($constant);
	}



	/**
	 * @return \Kdyby\Migrations\History
	 */
	public function createHistory()
	{
		return new History($this, $this->migrationVersion);
	}



	/**
	 * @param \Kdyby\Migrations\Version|NULL $version
	 *
	 * @throws \Kdyby\Migrations\MigrationException
	 */
	public function setVersion(Version $version = NULL)
	{
		if ($version === NULL) {
			$this->log[] = new MigrationLog($this, $version);
			$this->migrationVersion = NULL;
			$this->lastUpdate = new \DateTime();
			return;
		}

		if ($version->getVersion() == $this->migrationVersion) {
			return;
		}

		/** @var \Kdyby\Migrations\History $history */
		$history = $version->getHistory();
		if ($history->getPackage() !== $this) {
			$packageClass = $history->getPackage()->getClassName();
			throw new MigrationException(
				'Package of given version ' . get_class($version) .
				' is not "' . $this->className . '", "' . $packageClass . '" given.'
			);
		}

		$this->log[] = new MigrationLog($this, $version);
		$this->migrationVersion = $version->getVersion();
		$this->lastUpdate = new \DateTime;
	}



	/**
	 * @return \Kdyby\Migrations\MigrationLog[]
	 */
	public function getMigrationsLog()
	{
		return $this->log->toArray();
	}

}
