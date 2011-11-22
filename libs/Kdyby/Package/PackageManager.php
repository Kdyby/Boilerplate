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
use Nette\Utils\Finder;
use Symfony;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PackageManager extends Nette\Object
{

	/** @var string */
	private $appDir;

	/** @var array */
	private $instances = array();

	/** @var array */
	private $metas = array();

	/** @var IMetadataProvider */
	private $metadataProvider;



	/**
	 * @param string $appDir
	 */
	public function __construct($appDir)
	{
		$this->appDir = $appDir;
		$this->metadataProvider = new MetadataStorage\FactoryStorage();
	}



	/**
	 * @param IMetadataProvider $provider
	 */
	public function setMetadataProvider(IMetadataProvider $provider)
	{
		$this->metadataProvider = $provider;
	}



	/**
	 * @param array $packages
	 * @return ApplicationEventInvoker
	 */
	public function createInvoker($packages)
	{
		return new ApplicationEventInvoker($packages);
	}



	/**
	 * @param string $packageName
	 * @return IPackage
	 */
	public function getInstance($packageName)
	{
		if (isset($this->instances[$packageName])) {
			return $this->instances[$packageName];
		}

		return $this->instances[$packageName] = $this->getPackageMeta($packageName)->newInstance();
	}



	/**
	 * @param string $packageName
	 * @return PackageMeta
	 */
	public function getPackageMeta($packageName)
	{
		$lName = strtolower(trim($packageName, '\\'));
		if (!isset($this->metas[$lName])) {
			$this->metas[$lName] = $this->metadataProvider->load($packageName);
		}

		return $this->metas[$lName];
	}

}
