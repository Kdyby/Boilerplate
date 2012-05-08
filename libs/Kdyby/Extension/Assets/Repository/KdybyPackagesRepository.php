<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets\Repository;

use Kdyby;
use Kdyby\Extension\Assets;
use Kdyby\Packages\PackageManager;
use Nette;
use Nette\Utils\Json;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
final class KdybyPackagesRepository extends PackagesRepository
{
	/**
	 * @var \Kdyby\Packages\PackageManager
	 */
	private $packageManager;



	/**
	 * @param \Kdyby\Packages\PackageManager $packageManager
	 */
	public function __construct(PackageManager $packageManager)
	{
		$this->packageManager = $packageManager;
	}



	/**
	 * @param string $name
	 * @param string $version
	 *
	 * @return bool
	 */
	public function hasAsset($name, $version = NULL)
	{
		$this->loadAssets();
		return parent::hasAsset($name, $version);
	}



	/**
	 * @param string $name
	 * @param string $version
	 *
	 * @return \Kdyby\Extension\Assets\Repository\AssetPackage
	 */
	public function getAsset($name, $version = NULL)
	{
		$this->loadAssets();
		return parent::getAsset($name, $version);
	}



	/**
	 * Crawls registered packages and loads javascript resources information.
	 */
	protected function loadAssets()
	{
		if ($this->assets) {
			return;
		}

		foreach ($this->packageManager->getPackages() as $package) {
			if (!file_exists($definitionFile = $package->getPath() . '/Resources/assets.json')) {
				continue;
			}

			$this->registerAssetsFile($definitionFile);
		}
	}

}
