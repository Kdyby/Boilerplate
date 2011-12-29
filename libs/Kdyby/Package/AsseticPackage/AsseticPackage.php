<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\AsseticPackage;

use Kdyby;
use Nette;
use Nette\Application\Routers\Route;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AsseticPackage extends Kdyby\Packages\Package
{

	/**
	 * @param \Nette\Config\Configurator $config
	 * @param \Nette\Config\Compiler $compiler
	 */
	public function compile(Nette\Config\Configurator $config, Nette\Config\Compiler $compiler)
	{
		$compiler->addExtension('assetic', new DI\AsseticExtension());
	}



	/**
	 */
	public function startup()
	{
		if ($this->container->parameters['assetic_debug']) {
			$router = $this->container->router;
			$router[] = new Route("/<path .*>", array(
				'presenter' => 'AsseticPackage:AsseticPresenter',
			));
		}
	}

}
