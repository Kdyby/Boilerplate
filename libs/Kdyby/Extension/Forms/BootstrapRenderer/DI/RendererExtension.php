<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Forms\BootstrapRenderer\DI;

use Kdyby;
use Nette\Config\Compiler;
use Nette\Config\Configurator;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RendererExtension extends Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$engine = $builder->getDefinition('nette.latte');

		$install = 'Kdyby\Extension\Forms\BootstrapRenderer\Latte\FormMacros::install';
		$engine->addSetup($install . '(?->compiler)', array('@self'));
	}



	/**
	 * @param \Nette\Config\Configurator $config
	 */
	public static function register(Configurator $config)
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) {
			$compiler->addExtension('twBootstrapRenderer', new RendererExtension());
		};
	}

}
