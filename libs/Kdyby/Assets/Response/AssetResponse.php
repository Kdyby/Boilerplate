<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
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
