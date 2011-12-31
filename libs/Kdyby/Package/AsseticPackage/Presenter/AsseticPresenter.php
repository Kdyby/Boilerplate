<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\AsseticPackage\Presenter;

use Kdyby;
use Kdyby\Assets\Response\AssetResponse;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AsseticPresenter extends Nette\Object implements Nette\Application\IPresenter
{

	/** @var \Kdyby\Assets\Storage\CacheStorage */
	private $storage;



	/**
	 * @param \Kdyby\Assets\Storage\CacheStorage $storage
	 */
	public function __construct(Kdyby\Assets\Storage\CacheStorage $storage)
	{
		$this->storage = $storage;
	}



	/**
	 * @param \Nette\Application\Request $request
	 *
	 * @return \Nette\Application\IResponse
	 */
	public function run(Nette\Application\Request $request)
	{
		$path = trim($request->parameters['prefix'], '/');
		$path .= '/' . trim($request->parameters['name'], '/');
		return new AssetResponse($this->storage, $path);
	}

}
