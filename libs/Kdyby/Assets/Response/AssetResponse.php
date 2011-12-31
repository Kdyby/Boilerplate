<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Assets\Response;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read string $file
 * @property-read string $contentType
 */
class AssetResponse extends Nette\Object implements Nette\Application\IResponse
{
	/** @var \Kdyby\Assets\Storage\CacheStorage */
	private $storage;

	/** @var string */
	private $assetOutput;



	/**
	 * @param \Kdyby\Assets\Storage\CacheStorage $storage
	 * @param string $assetOutput
	 */
	public function __construct(Kdyby\Assets\Storage\CacheStorage $storage, $assetOutput)
	{
		$this->storage = $storage;
		$this->assetOutput = $assetOutput;
	}



	/**
	 * Sends response to output.
	 *
	 * @param \Nette\Http\IRequest $httpRequest
	 * @param \Nette\Http\IResponse $httpResponse
	 *
	 * @return void
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		$httpResponse->setContentType($this->storage->getContentType($this->assetOutput));
		$httpResponse->setHeader('Content-Disposition', 'inline');

		echo $this->storage->readAsset($this->assetOutput);
	}

}
