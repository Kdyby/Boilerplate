<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets\Storage;

use Assetic;
use Assetic\Asset\AssetInterface;
use Kdyby;
use Kdyby\Extension\Assets;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class PublicStorage extends Nette\Object implements Kdyby\Extension\Assets\IStorage
{

	/** @var string */
	private $dir;

	/** @var string */
	private $baseUrl;



	/**
	 * @param string $dir
	 * @param \Nette\Http\Request $httpRequest
	 */
	public function __construct($dir, Nette\Http\Request $httpRequest)
	{
		$this->dir = $dir;
		$this->baseUrl = rtrim($httpRequest->getUrl()->getBaseUrl(), '/');
	}



	/**
	 * @param \Assetic\AssetManager $am
	 */
	public function writeManagerAssets(Assetic\AssetManager $am)
	{
		foreach ($am->getNames() as $name) {
			$this->writeAsset($am->get($name));
		}
	}



	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 */
	public function writeAsset(AssetInterface $asset)
	{
		Kdyby\Tools\Filesystem::write($this->dir . '/' . $asset->getTargetPath(), $asset->dump());
	}



	/**
	 * @param string $assetOutput
	 *
	 * @throws \Kdyby\Extension\Assets\NotSupportedException
	 * @return \Assetic\Asset\AssetInterface
	 */
	public function readAsset($assetOutput)
	{
		throw new Assets\NotSupportedException("Not supported");
	}



	/**
	 * @param string|\Assetic\Asset\AssetInterface $asset
	 *
	 * @return string
	 */
	public function getAssetUrl($asset)
	{
		if ($asset instanceof AssetInterface) {
			$asset = $asset->getTargetPath();
		}

		return $this->baseUrl . '/' . $asset;
	}



	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 *
	 * @return bool
	 * @throws \Kdyby\InvalidStateException
	 */
	public function isFresh(AssetInterface $asset)
	{
		$file = $this->dir . '/' . $asset->getTargetPath();
		if (!file_exists($file)) {
			return FALSE;
		}

		return filemtime($file) > $asset->getLastModified();
	}

}
