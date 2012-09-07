<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets\Repository;

use Assetic;
use Kdyby;
use Kdyby\Extension\Assets;
use Nette;
use Nette\Utils\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class AssetFile extends Nette\Object
{

	/**
	 * @var string
	 */
	public $input;

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var array
	 */
	public $filters = array();

	/**
	 * @var string
	 */
	public $serialized;



	/**
	 * @param string $input
	 * @param string $type
	 * @param array $options
	 * @param array $filters
	 */
	public function __construct($input, $type = Assets\FormulaeManager::TYPE_JAVASCRIPT, array $options = array(), array $filters = array())
	{
		$this->input = $input;
		$this->type = $type;
		$this->options = $options;
		$this->filters = $filters;
	}



	/**
	 * @param \Kdyby\Extension\Assets\AssetFactory $factory
	 *
	 * @throws \Kdyby\Extension\Assets\FileNotFoundException
	 * @return \Assetic\Asset\AssetCollection
	 */
	public function createAsset(Assets\AssetFactory $factory)
	{
		/** @var \Assetic\Asset\AssetCollection $asset */
		foreach ($asset = $factory->createAsset($this->input, $this->filters, $this->options) as $leaf) {
			if (!$leaf instanceof Assetic\Asset\FileAsset) {
				continue;
			}

			/** @var \Assetic\Asset\FileAsset $leaf */
			if (!file_exists($file = $leaf->getSourceRoot() . '/' . $leaf->getSourcePath())) {
				throw new Assets\FileNotFoundException('Assetic wasn\'t able to process your input, file "' . $file . '" doesn\'t exists.');
			}
		}

		if ($asset instanceof Assetic\Asset\AssetInterface && !isset($this->options['output'])) {
			$this->options['output'] = $asset->getTargetPath();
		}

		return $asset;
	}



	/**
	 * @param \Kdyby\Extension\Assets\AssetFactory $factory
	 *
	 * @return string
	 */
	public function serialize(Assets\AssetFactory $factory)
	{
		$assets = array();
		foreach ($asset = $this->createAsset($factory) as $leaf) {
			$assets[] = Code\Helpers::formatArgs('unserialize(?)', array(serialize($leaf)));
		}

		if (count($assets) === 1) {
			return reset($assets);
		}

		$assets = "array(\n\t". implode(",\n\t", $assets) . "\n)";
		return 'new Assetic\Asset\AssetCollection(' . $assets . ')';
	}

}
