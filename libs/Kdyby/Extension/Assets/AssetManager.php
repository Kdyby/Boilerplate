<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets;

use Assetic;
use Assetic\Asset\AssetInterface;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetManager extends Assetic\AssetManager
{

	/** @var array */
	private $options = array();

	/** @var array */
	private $filters = array();

	/** @var array */
	private $outputs = array();



	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 * @param array $filters
	 * @param array $options
	 *
	 * @return string
	 */
	public function add(AssetInterface $asset, $filters = array(), $options = array())
	{
		parent::set($name = (count($this->getNames())+1), $asset);

		$this->options[$name] = $options;
		$this->filters[$name] = $filters;
		$this->outputs[$asset->getTargetPath()] = $name;

		return $name;
	}



	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 *
	 * @return mixed
	 * @throws \Kdyby\InvalidArgumentException
	 */
	public function getAssetName(AssetInterface $asset)
	{
		foreach ($this->getNames() as $name) {
			if ($this->get($name) === $asset) {
				return $name;
			}
		}

		throw new Kdyby\InvalidArgumentException('Asset is not registered.');
	}



	/**
	 * @param string $output
	 * @return \Assetic\Asset\AssetInterface
	 */
	public function getOutputAsset($output)
	{
		return $this->get($this->outputs[$output]);
	}



	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getOptions($name)
	{
		$this->get($name);
		return $this->options[$name];
	}



	/**
	 * @param string $name
	 * @return array
	 */
	public function getFilters($name)
	{
		$this->get($name);
		return $this->filters[$name];
	}

}
