<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Extension\Assets;

use Assetic;
use Assetic\Asset\AssetInterface;
use Kdyby;
use Nette;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Arrays;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FormulaeManager extends Nette\Object
{
	const TYPE_STYLESHEET = 'css';
	const TYPE_JAVASCRIPT = 'js';

	/**
	 * @var \Kdyby\Extension\Assets\IStorage
	 */
	private $storage;

	/**
	 * @var \Kdyby\Extension\Assets\AssetManager
	 */
	private $assetManager;

	/**
	 * @var \Kdyby\Extension\Assets\FilterManager
	 */
	private $filterManager;

	/**
	 * @var \Kdyby\Extension\Assets\IAssetRepository
	 */
	private $repository;

	/**
	 * @var bool
	 */
	private $debug = FALSE;

	/**
	 * @var array
	 */
	private $presenterTypes = array(
		self::TYPE_STYLESHEET => array(),
		self::TYPE_JAVASCRIPT => array()
	);

	/**
	 * @var array
	 */
	private $componentTypes = array(
		self::TYPE_STYLESHEET => array(),
		self::TYPE_JAVASCRIPT => array()
	);

	/**
	 * @var array
	 */
	private $packagesRequiredBy = array();

	/**
	 * @var array
	 */
	private $resolved = array(
		self::TYPE_STYLESHEET => array(),
		self::TYPE_JAVASCRIPT => array()
	);



	/**
	 * @param \Kdyby\Extension\Assets\IStorage $storage
	 * @param \Kdyby\Extension\Assets\AssetManager $assetManager
	 * @param \Kdyby\Extension\Assets\FilterManager $filterManager
	 */
	public function __construct(IStorage $storage, AssetManager $assetManager, FilterManager $filterManager)
	{
		$this->storage = $storage;
		$this->assetManager = $assetManager;
		$this->filterManager = $filterManager;
	}



	/**
	 * @param \Kdyby\Extension\Assets\IAssetRepository $provider
	 */
	public function setJavascriptProvider(IAssetRepository $provider)
	{
		$this->repository = $provider;
	}



	/**
	 * @param bool $debug
	 */
	public function setDebug($debug = TRUE)
	{
		$this->debug = (bool)$debug;
	}



	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 * @param string $type
	 * @param array $filters
	 * @param array $options
	 * @param \Nette\ComponentModel\IComponent|null $presenterComponent
	 *
	 * @return string
	 */
	public function register(AssetInterface $asset, $type, $filters = array(), $options = array(), IComponent $presenterComponent = NULL)
	{
		if (isset($options['output'])) {
			$asset->setTargetPath($options['output']);
		}

		$name = $this->assetManager->add($asset, $filters, $options);
		if (!empty($options['requiredBy'])) {
			foreach ($options['requiredBy'] as $requiredBy) {
				$this->packagesRequiredBy[$requiredBy][] = $name;
			}

		} else {
			if ($presenterComponent instanceof Nette\Application\IPresenter) {
				$this->presenterTypes[$type][] = $name;

			} else {
				$this->componentTypes[$type][] = $name;
			}
		}

		return $name;
	}



	/**
	 * @param string $name
	 * @param string $version
	 */
	public function requireAsset($name, $version = NULL)
	{
		if (!$this->repository) {
			throw new Kdyby\InvalidStateException("No implementation of IJavascriptProvider was given.");
		}

		$this->componentTypes[$name] = $version;
	}



	/**
	 * @param string $type
	 * @return array[]
	 */
	public function getAssets($type)
	{
		return array_reverse($this->resolved[$type]);
	}



	/**
	 * @param string $name
	 * @return array
	 */
	public function getAssetInfo($name)
	{
		$asset = $this->assetManager->get($name);
		if ($asset instanceof Assetic\Asset\AssetCollection) {
			/** @var \Assetic\Asset\AssetCollection $asset */
			foreach ($asset as $one) {
				$asset = $one;
				break;
			}
		}

		return array(
			'source' => $asset->getSourcePath(),
			'src' => $this->storage->getAssetUrl($this->assetManager->get($name))
		) + $this->assetManager->getOptions($name);
	}



	/**
	 * When registered asset is not fresh, writes it to storage
	 */
	public function publish()
	{
		$types = array_unique(array_merge(
			Arrays::flatten($this->presenterTypes),
			Arrays::flatten($this->componentTypes)
		));

		foreach ($types as $name) {
			$this->publishAndResolve($name);
		}
	}



	/**
	 * @param $name
	 * @return mixed
	 */
	private function publishAndResolve($name)
	{
		$asset = $this->assetManager->get($name);
		$info = $this->getAssetInfo($name);

		if (isset($info['name']) && isset($this->packagesRequiredBy[$info['name']])) {
			foreach ($this->packagesRequiredBy[$info['name']] as $requiredName) {
				$this->publishAndResolve($requiredName);
			}
		}

		// register resolved
		if (!in_array($extension = pathinfo($info['src'], PATHINFO_EXTENSION), array_keys($this->resolved))) {
			$extension = pathinfo($info['source'], PATHINFO_EXTENSION);
		}
		if (isset($info['name'])) {
			$this->resolved[$extension][$info['name']] = $info;

		} else {
			$this->resolved[$extension][$info['source']] = $info;
		}

		// if files is not fresh or published, make it right
		if ($this->storage->isFresh($asset)) {
			return;
		}

		// ensure filters before write
		foreach ($this->assetManager->getFilters($name) as $filter) {
			if ('?' != $filter[0]) {
				$asset->ensureFilter($this->filterManager->get($filter));

			} elseif ($this->debug) {
				$asset->ensureFilter($this->filterManager->get(substr($filter, 1)));
			}
		}

		$this->storage->writeAsset($asset);
	}

}
