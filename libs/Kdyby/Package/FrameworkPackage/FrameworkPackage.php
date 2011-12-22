<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\FrameworkPackage;

use Kdyby;
use Nette;
use Symfony\Component\DependencyInjection\ContainerBuilder;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FrameworkPackage extends Kdyby\Packages\Package
{

	/**
	 * Occurs before the application loads presenter
	 */
	public function startup()
	{
//		if ($this->container->session->exists()) {
//			$this->container->session->start();
//		}
	}



	/**
	 * @param \Nette\Config\Configurator $config
	 * @param \Nette\Config\Compiler $compiler
	 */
	public function compile(Nette\Config\Configurator $config, Nette\Config\Compiler $compiler)
	{
		$compiler->addExtension('kdyby', new DI\FrameworkExtension());
	}

}
