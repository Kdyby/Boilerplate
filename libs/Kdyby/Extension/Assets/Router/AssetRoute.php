<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets\Router;

use Kdyby;
use Kdyby\Extension\Assets;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class AssetRoute extends Nette\Application\Routers\Route
{

	/**
	 * @param string $prefix
	 * @param \Kdyby\Extension\Assets\IStorage $storage
	 */
	public function __construct($prefix, Assets\IStorage $storage)
	{
		parent::__construct('<prefix ' . $prefix . '>/<name .*>', array(
			static::PRESENTER_KEY => 'Nette:Micro',
			'callback' => new Assets\Responder\AssetResponder($storage),
		));
	}

}
