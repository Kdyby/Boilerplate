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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TemplateConfigurator extends Nette\Object implements ITemplateConfigurator
{
	/** @var array */
	private $macroFactories = array();

	/** @var \SystemContainer|\Nette\DI\Container */
	private $container;



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
		$this->macroFactories[] = callback($this->container, 'create' . ucfirst($factory));
	}



	/**
	 * @param \Nette\Templating\Template $template
	 */
	public function configure(Nette\Templating\Template $template)
	{
		$template->registerHelperLoader('Kdyby\Templating\DefaultHelpers::loader');
		$template->_fm = $this->container->assetic_formulaeManager;
	}



	/**
	 * @param \Nette\Templating\Template $template
	 */
	public function prepareFilters(Nette\Templating\Template $template)
	{
		$latte = new Nette\Latte\Engine();
		foreach ($this->macroFactories as $factory) {
			$factory($latte->parser);
		}

		$template->registerFilter($latte);
	}

}
