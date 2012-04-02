<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Extension\Assets\Storage;

use Assetic;
use Assetic\Asset\AssetInterface;
use Kdyby;
use Kdyby\Tools\Filesystem;
use Kdyby\Tools\MimeTypeDetector;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CacheStorage extends Nette\Object implements Kdyby\Extension\Assets\IStorage
{

	/** @var string */
	private $cache;

	/** @var string */
	private $tempDir;

	/** @var string */
	private $baseUrl;



	/**
	 * @param \Nette\Caching\Storages\FileStorage $storage
	 * @param string $tempDir
	 * @param \Nette\Http\Request $httpRequest
	 */
	public function __construct(FileStorage $storage, $tempDir, Nette\Http\Request $httpRequest)
	{
		$this->cache = new Cache($storage, 'Assetic');
		$this->tempDir = $tempDir;
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
		// prepare
		$tempFile = $this->tempDir . '/' . basename($asset->getTargetPath());
		Filesystem::write($tempFile, $assetDump = $asset->dump());
		$contentType = MimeTypeDetector::fromFile($tempFile);

		// store
		$this->cache->save($asset->getTargetPath(), $assetDump, $dp = array(
			Cache::FILES => (array)$this->getAssetDeps($asset)
		));
		$this->cache->save('Content-Type:' . $asset->getTargetPath(), $contentType, $dp);

		// cleanup
		Filesystem::rm($tempFile);
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
		return $this->cache->load($asset->getTargetPath()) !== NULL;
	}



	/**
	 * @param string $assetOutput
	 *
	 * @return string
	 */
	public function readAsset($assetOutput)
	{
		return $this->cache->load($assetOutput);
	}



	/**
	 * @param string $assetOutput
	 *
	 * @return string
	 */
	public function getContentType($assetOutput)
	{
		return $this->cache->load('Content-Type:' . $assetOutput);
	}



	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 *
	 * @return array|string
	 */
	private function getAssetDeps(AssetInterface $asset)
	{
		if (!$asset instanceof Assetic\Asset\AssetCollectionInterface) {
			return $asset->getSourceRoot() . '/' . $asset->getSourcePath();
		}

		$deps = array();
		foreach ($asset as $leaf) {
			$deps[] = $this->getAssetDeps($leaf);
		}

		return $deps;
	}

}
