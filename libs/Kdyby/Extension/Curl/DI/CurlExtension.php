<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Curl\DI;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CurlExtension extends Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('curl'))
			->setClass('Kdyby\Extension\Curl\CurlSender');

		$builder->addDefinition($this->prefix('curl.panel'))
			->setFactory('Kdyby\Extension\Curl\Diagnostics\Panel::register')
			->addTag('run', TRUE);
	}

}
