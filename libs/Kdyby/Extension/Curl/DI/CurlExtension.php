<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Extension\Curl\DI;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CurlExtension extends Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('curl'))
			->setClass('Kdyby\Extension\Curl\CurlSender');

		$builder->addDefinition($this->prefix('browser.panel'))
			->setFactory('Kdyby\Extension\Browser\Diagnostics\Panel::register')
			->addTag('run', TRUE);

		$builder->addDefinition($this->prefix('curl.panel'))
			->setFactory('Kdyby\Extension\Curl\Diagnostics\Panel::register')
			->addTag('run', TRUE);
	}

}
