<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets\Responder;

use Kdyby;
use Kdyby\Extension\Assets;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class AssetResponder extends Nette\Object
{

	/** @var \Kdyby\Extension\Assets\Storage\CacheStorage */
	private $storage;



	/**
	 * @param \Kdyby\Extension\Assets\IStorage $storage
	 */
	public function __construct(Assets\IStorage $storage)
	{
		$this->storage = $storage;
	}



	/**
	 * @param string $prefix
	 * @param string $name
	 *
	 * @return \Kdyby\Extension\Assets\Response\AssetResponse
	 */
	public function __invoke($prefix, $name)
	{
		return new Assets\Response\AssetResponse($this->storage, trim($prefix, '/') . '/' . trim($name, '/'));
	}

}
