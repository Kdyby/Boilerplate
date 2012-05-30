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
use Nette;
use Nette\Utils\Arrays;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetPackage extends Nette\Object
{

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $version = 'latest';

	/**
	 * @var array
	 */
	private $paths = array(
		Assets\FormulaeManager::TYPE_JAVASCRIPT => array(),
		Assets\FormulaeManager::TYPE_STYLESHEET => array(),
	);

	/**
	 * @var array
	 */
	public $require = array();



	/**
	 * @param string $path
	 * @param string $type
	 *
	 * @return \Kdyby\Extension\Assets\Repository\AssetFile
	 */
	public function addPath($path, $type)
	{
		$this->addFile($asset = new AssetFile($path, $type));
		return $asset;
	}



	/**
	 * @param \Kdyby\Extension\Assets\Repository\AssetFile $file
	 */
	public function addFile(AssetFile $file)
	{
		$this->paths[$file->type][] = $file;
		$file->options['name'] = $this->name;
		$file->options['require'] = array_keys($this->require);
	}



	/**
	 * @return array|\Kdyby\Extension\Assets\Repository\AssetFile[]
	 */
	public function getFiles()
	{
		return array_reverse(Arrays::flatten($this->paths));
	}



	/**
	 * @param \Kdyby\Extension\Assets\IAssetRepository $repository
	 *
	 * @return array|\Kdyby\Extension\Assets\Repository\AssetPackage[]
	 */
	public function getDependencies(Assets\IAssetRepository $repository)
	{
		$dependencies = array();
		foreach ($this->require as $dependency => $version) {
			$dependencies[] = $repository->getAsset($dependency, $version);
		}

		return $dependencies;
	}



	/**
	 * @param \Kdyby\Extension\Assets\IAssetRepository $repository
	 * @param array $resolved
	 *
	 * @return array|\Kdyby\Extension\Assets\Repository\AssetFile[]
	 */
	public function resolveFiles(Assets\IAssetRepository $repository, $resolved = array())
	{
		$files = array();

		$resolve = array($this);
		do {
			/** @var \Kdyby\Extension\Assets\Repository\AssetPackage $resolving */
			$resolved[] = $resolving = array_shift($resolve);

			foreach ($resolving->getFiles() as $file) {
				$files[] = $file = clone $file;

				if ($resolving !== $this) {
					$file->options['requiredBy'][] = $this->name;
				}
			}

			foreach ($resolving->getDependencies($repository) as $dependency) {
				if (in_array($dependency, $resolved, TRUE)) {
					continue;
				}

				$resolve[] = $dependency;
			}

		} while($resolve);

		return $files;
	}

}
