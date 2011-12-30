<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Assets;

use Assetic;
use Assetic\Asset\AssetInterface;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FormulaeManager extends Nette\Object
{
	const TYPE_STYLESHEET = 'css';
	const TYPE_JAVASCRIPT = 'js';

	/** @var \Kdyby\Assets\IStorage */
	private $storage;

	/** @var \Kdyby\Assets\AssetManager */
	private $assetManager;

	/** @var \Kdyby\Assets\FilterManager */
	private $filterManager;

	/** @var array */
	private $types = array(
		self::TYPE_STYLESHEET => array(),
		self::TYPE_JAVASCRIPT => array()
	);

	/** @var bool */
	private $debug = FALSE;



	/**
	 * @param \Kdyby\Assets\IStorage $storage
	 * @param \Kdyby\Assets\AssetManager $assetManager
	 * @param \Kdyby\Assets\FilterManager $filterManager
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
	 */
	public function register(AssetInterface $asset, $type, $filters = array(), $options = array())
	{
		$this->types[$type][] = $this->assetManager->add($asset, $filters, $options);
	}



	/**
	 * @param string $type
	 * @return array[]
	 */
	public function getAssets($type)
	{
		$assets = array();
		foreach ($this->types[$type] as $name) {
			$assets[] = array(
				'src' => $this->storage->getAssetUrl($this->assetManager->get($name))
			) + $this->assetManager->getOptions($name);
		}

		return $assets;
	}



	/**
	 * When registered asset is not fresh, writes it to storage
	 */
	public function publish()
	{
		foreach (Kdyby\Tools\Arrays::flatMap($this->types) as $name) {
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
