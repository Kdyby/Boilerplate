<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets\Storage;

use Assetic;
use Assetic\Asset\AssetInterface;
use Kdyby;
use Kdyby\Extension\Curl;
use Kdyby\Extension\Assets;
use Kdyby\Tools\Filesystem;
use Kdyby\Tools\MimeTypeDetector;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CacheStorage extends Nette\Object implements Kdyby\Extension\Assets\IStorage
{

	/**
	 * @var bool
	 */
	private static $rewriteIsWorking = FALSE;

	/**
	 * @var string
	 */
	private $cache;

	/**
	 * @var string
	 */
	private $tempDir;

	/**
	 * @var string
	 */
	private $baseUrl;



	/**
	 * @param \Nette\Caching\IStorage $storage
	 * @param string $tempDir
	 * @param \Nette\Http\Request $httpRequest
	 */
	public function __construct(IStorage $storage, $tempDir, Nette\Http\Request $httpRequest)
	{
		$this->cache = new Cache($storage, 'Kdyby.Assets');
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
	 *
	 * @throws \Kdyby\Extension\Assets\NotSupportedException
	 * @return
	 */
	public function writeAsset(AssetInterface $asset)
	{
		// prepare
		$tempFile = $this->tempDir . '/' . basename($asset->getTargetPath());
		Filesystem::write($tempFile, $assetDump = $asset->dump());
		$contentType = MimeTypeDetector::fromFile($tempFile);

		// store
		$this->cache->save($contentKey = $asset->getTargetPath(), $assetDump, $dp = array(
			Cache::FILES => (array)$this->getAssetDeps($asset)
		));
		$this->cache->save($metaKey = 'Content-Type:' . $asset->getTargetPath(), $contentType, $dp);

		// cleanup
		Filesystem::rm($tempFile);

		if (static::$rewriteIsWorking === TRUE) {
			return;
		}

		try {
			$test = new Curl\Request($this->getAssetUrl($asset));
			$test->method = 'HEAD';
			$test->setSender($tester = new Curl\CurlSender());
			$tester->timeout = 5;

			static::$rewriteIsWorking = (bool)$test->send();

		} catch (Curl\CurlException $e) {
			$this->cache->remove($contentKey);
			$this->cache->remove($metaKey);

			$class = get_called_class();
			throw new Assets\NotSupportedException(
				"Your current server settings doesn't allow to properly link assets. " .
				"$class requires working rewrite technology, " .
				"e.g. mod_rewrite on Apache or properly configured nginx.", 0, $e
			);
		}
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
