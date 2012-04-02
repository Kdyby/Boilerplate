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

	/** @var \Kdyby\Extension\Assets\IStorage */
	private $storage;

	/** @var \Kdyby\Extension\Assets\AssetManager */
	private $assetManager;

	/** @var \Kdyby\Extension\Assets\FilterManager */
	private $filterManager;

	/** @var array */
	private $presenterTypes = array(
		self::TYPE_STYLESHEET => array(),
		self::TYPE_JAVASCRIPT => array()
	);

	/** @var array */
	private $componentTypes = array(
		self::TYPE_STYLESHEET => array(),
		self::TYPE_JAVASCRIPT => array()
	);

	/** @var bool */
	private $debug = FALSE;



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
		if ($presenterComponent instanceof Nette\Application\IPresenter) {
			$this->presenterTypes[$type][] = $name;

		} else {
			$this->componentTypes[$type][] = $name;
		}

		return $name;
	}



	/**
	 * @param string $type
	 * @return array[]
	 */
	public function getAssets($type)
	{
		$assets = array();
		foreach ($this->componentTypes[$type] as $name) {
			$assets[] = $this->getAssetInfo($name);
		}
		foreach ($this->presenterTypes[$type] as $name) {
			$assets[] = $this->getAssetInfo($name);
		}

		return array_reverse($assets);
	}



	/**
	 * @param string $name
	 * @return array
	 */
	public function getAssetInfo($name)
	{
		$all = $this->assetManager->get($name)->all();
		return array(
			'source' => reset($all)->getSourcePath(),
			'src' => $this->storage->getAssetUrl($this->assetManager->get($name))
		) + $this->assetManager->getOptions($name);
	}



	/**
	 * When registered asset is not fresh, writes it to storage
	 */
	public function publish()
	{
		$types = array_merge(
			Arrays::flatten($this->presenterTypes),
			Arrays::flatten($this->componentTypes)
		);

		foreach ($types as $name) {
			$asset = $this->assetManager->get($name);
			if ($this->storage->isFresh($asset)) {
				continue;
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

}
