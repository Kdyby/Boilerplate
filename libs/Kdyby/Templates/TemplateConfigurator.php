<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Templates;

use Kdyby;
use Nette;
use Nette\Latte;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TemplateConfigurator extends Nette\Object
{
	/** @var array */
	private $macroFactories = array();

	/** @var \SystemContainer|\Nette\DI\Container */
	private $container;

	/** @var \Nette\Latte\Engine */
	private $latte;



	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}



	/**
	 * @param string $factory
	 */
	public function addFactory($factory)
	{
		$this->macroFactories[] = $factory;
	}



	/**
	 * @param \Nette\Templating\Template $template
	 */
	public function configure(Nette\Templating\Template $template)
	{
		$template->registerHelperLoader('Kdyby\Templates\DefaultHelpers::loader');
	}



	/**
	 * @param \Nette\Templating\Template $template
	 */
	public function prepareFilters(Latte\Engine $latte)
	{
		$this->latte = $latte;
		foreach ($this->macroFactories as $factory) {
			if (!$this->container->hasService($factory)) {
				continue;
			}

			$this->container->$factory->invoke($this->latte->getCompiler());
		}
	}



	/**
	 * Returns Latter parser for the last prepareFilters call.
	 *
	 * @return \Nette\Latte\Engine
	 */
	public function getLatte()
	{
		return $this->latte;
	}

}
