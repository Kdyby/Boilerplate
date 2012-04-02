<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Extension\Assets\Router;

use Kdyby;
use Kdyby\Extension\Assets;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetRoute extends Nette\Application\Routers\Route
{

	/**
	 * @param string $prefix
	 * @param \Kdyby\Extension\Assets\Storage\CacheStorage $storage
	 */
	public function __construct($prefix, Assets\Storage\CacheStorage $storage)
	{
		parent::__construct('<prefix ' . $prefix . '>/<name .*>', array(
			static::PRESENTER_KEY => 'Nette:Micro',
			'callback' => callback(new Assets\Responder\AssetResponder($storage), '__invoke'),
		));
	}

}
