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
use Nette\Utils\Arrays;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Nette\Utils\Validators;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PackagesRepository extends Nette\Object implements Kdyby\Extension\Assets\IAssetRepository
{
	/**
	 * @var array
	 */
	public static $extensionsMap = array(
		'js' => Assets\FormulaeManager::TYPE_JAVASCRIPT,
		'css' => Assets\FormulaeManager::TYPE_STYLESHEET,
	);

	/**
	 * @var array|\Kdyby\Extension\Assets\AssetPackage[]
	 */
	protected $assets = array();



	/**
	 * Resolvers, whether or not, the repository provides the script.
	 *
	 * @param string $name
	 * @param null $version
	 *
	 * @return boolean
	 */
	public function hasAsset($name, $version = NULL)
	{
		if (!isset($this->assets[$name = strtolower($name)])) {
			return FALSE;
		}

		if ($version === '*' || $version === 'latest') {
			return TRUE;
		}

		if ($version !== NULL && !isset($this->assets[$name][$version])) {
			return FALSE;
		}

		return TRUE;
	}



	/**
	 * @param string $name
	 * @param string $version
	 *
	 * @throws \Kdyby\Extension\Assets\AssetNotFoundException
	 * @return mixed
	 */
	public function getAsset($name, $version = NULL)
	{
		if (!$this->hasAsset($name = strtolower($name), $version)) {
			throw new Assets\AssetNotFoundException("Assets {$name} are not registered.");
		}

		$versions = $this->assets[$name];
		if ($version !== NULL && $version !== 'latest' && $version !== '*') {
			return $this->assets[$name][$version];
		}
		ksort($versions);
		return end($versions);
	}



	/**
	 * @return \Kdyby\Extension\Assets\Repository\AssetPackage[]
	 */
	public function getAll()
	{
		return Arrays::flatten($this->assets);
	}



	/**
	 * @param string $definitionFile
	 *
	 * @throws \Kdyby\Extension\Assets\FileNotFoundException
	 * @throws \Kdyby\Extension\Assets\InvalidDefinitionFileException
	 */
	public function registerAssetsFile($definitionFile)
	{
		if (!file_exists($definitionFile)) {
			throw new Assets\FileNotFoundException("Definition file $definitionFile is missing.");
		}

		foreach (Json::decode(file_get_contents($definitionFile)) as $definition) {
			try {
				$this->registerAsset($this->createAsset($definition, $definitionFile));

			} catch (\Exception $e) {
				throw new Assets\InvalidDefinitionFileException($e->getMessage(), 0, $e);
			}
		}
	}



	/**
	 * @param \Kdyby\Extension\Assets\Repository\AssetPackage $asset
	 */
	public function registerAsset(AssetPackage $asset)
	{
		$this->assets[strtolower($asset->name)][strtolower($asset->version)] = $asset;
	}



	/**
	 * @internal
	 * @param array $definition
	 * @param string $definitionFile
	 *
	 * @throws \Kdyby\NotSupportedException
	 * @throws \Kdyby\UnexpectedValueException
	 * @throws \Kdyby\FileNotFoundException
	 * @throws \Nette\Utils\AssertionException
	 * @return \Kdyby\Extension\Assets\Repository\AssetPackage
	 */
	public static function createAsset($definition, $definitionFile = NULL)
	{
		$asset = new AssetPackage;
		$definition = (array)$definition;
		/** @var \Kdyby\Extension\Assets\Repository\AssetFile[] $files */
		$files = array();

		// name of asset
		Validators::assertField($definition, 'name',
			'string|pattern:[-a-z0-9]+/[-a-z0-9]+',
			"item '%' of asset definition, in $definitionFile"
		);
		$asset->name = $definition['name'];
		unset($definition['name']);

		// dependencies
		if (isset($definition['require'])) {
			$asset->require = (array)$definition['require'];
			unset($definition['require']);
		}

		// paths to include in page
		$baseDir = dirname($definitionFile);
		foreach ($definition['paths'] as $path) {
			if (!file_exists($assetPath = $baseDir . '/' . $path)) {
				throw new Assets\FileNotFoundException("Path '{$path}' of asset '{$asset->name}', in $definitionFile is not valid.");
			}
			$extension = pathinfo($assetPath, PATHINFO_EXTENSION);
			if (!isset(static::$extensionsMap[$extension]) && !isset($definition['filter'])) {
				throw new Assets\NotSupportedException("Cannot handle extension $extension of asset '{$asset->name}'.");
				$extension = static::$extensionsMap[$extension];
			}
			$files[$path] = $asset->addPath($assetPath, $extension);
		}
		unset($definition['paths']);

		// filters
		if (isset($definition['filter'])) {
			foreach ($definition['filter'] as $path => $filters) {
				$files[$path]->filters = (array)$filters;
			}
			unset($definition['filter']);

		} elseif (isset($definition['filters'])) {
			throw new Assets\UnexpectedValueException("Key 'filters' of asset '{$asset->name}' should be named 'filter'.");
		}

		// options
		if (isset($definition['options'])) {
			foreach ($definition['options'] as $path => $options) {
				$files[$path]->options = (array)$options;
			}
			unset($definition['options']);

		} elseif (isset($definition['option'])) {
			throw new Assets\UnexpectedValueException("Key 'option' of asset '{$asset->name}' should be named 'options'.");
		}

		// version
		if (isset($definition['version'])) {
			Validators::assertField($definition, 'version',
				'string|pattern:[0-9]+(\\.[0-9]+)+(-?[-a-z]+[0-9]*)?',
				"item '%' of asset '{$asset->name}', in $definitionFile"
			);
			$asset->version = $definition['version'];
			unset($definition['version']);
		}

		$definition = (array)$definition;
		if (!empty($definition)) {
			$keys = implode(', ', array_keys($definition));
			throw new Assets\UnexpectedValueException("Keys $keys in definition of asset '{$asset->name}', in $definitionFile are ambiguous.");
		}

		return $asset;
	}

}
