<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application\UI;

use Kdyby;
use Nette;
use Nette\Environment;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka
 *
 * @property-read Presenter $presenter
 * @property Kdyby\Templates\FileTemplate $template
 *
 * @method Presenter getPresenter() getPresenter()
 * @method Kdyby\Templates\FileTemplate getTemplate() getTemplate()
 */
abstract class Control extends Nette\Application\UI\Control
{

	/** @var Nette\DI\Container */
	private $context;



	/**
	 * @param Nette\ComponentModel\Container $obj
	 * @return type
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Nette\Application\UI\Presenter) {
			return;
		}

		$this->setContext($obj->getContext());
	}



	/**
	 * @param Nette\DI\Container $context
	 */
	public function setContext(Nette\DI\Container $context)
	{
		$this->context = $context;
	}



	/**
	 * @return Kdyby\DI\Container
	 */
	public function getContext()
	{
		if (!$this->context) {
			throw new Nette\InvalidStateException("Missing context, component wasn't yet attached to presenter.");
		}

		return $this->context;
	}



	/**
	 * @return Kdyby\Templating\FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		return $this->getContext()->templateFactory->createTemplate($this, $class);
	}

}