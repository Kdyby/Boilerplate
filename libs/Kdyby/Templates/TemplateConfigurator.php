<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Templates;

use Kdyby;
use Nette;
use Nette\Latte;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TemplateConfigurator extends Nette\Object implements ITemplateConfigurator
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
		$template->_fm = $this->container->assetic_formulaeManager;
	}



	/**
	 * @param \Nette\Templating\Template $template
	 */
	public function prepareFilters(Nette\Templating\Template $template)
	{
		$this->latte = new Latte\Engine();
		foreach ($this->macroFactories as $factory) {
			if (!$this->container->hasService($factory)) {
				continue;
			}

			$this->container->$factory->invoke($this->latte->getCompiler());
		}

		$template->registerFilter($this->latte);
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
