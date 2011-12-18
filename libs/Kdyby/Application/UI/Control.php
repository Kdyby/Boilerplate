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
use Kdyby\Templates\ITemplateFactory;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read \Kdyby\Application\UI\Presenter $presenter
 * @method \Kdyby\Application\UI\Presenter getPresenter() getPresenter()
 */
abstract class Control extends Nette\Application\UI\Control
{

	/** @var \Kdyby\Templates\ITemplateFactory */
	private $templateFactory;



	/**
	 * @param \Kdyby\Templates\ITemplateFactory $templateFactory
	 */
	public function setTemplateFactory(ITemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}



	/**
	 * @param string|null $class
	 *
	 * @return \Kdyby\Templating\Template
	 */
	protected function createTemplate($class = NULL)
	{
		if ($this->templateFactory === NULL) {
			return parent::createTemplate($class);
		}

		return $this->templateFactory->createTemplate($this, $class);
	}



	/**
	 * @param \Nette\ComponentModel\IComponent $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof Nette\Application\UI\Presenter) {
			$this->attachedToPresenter();
		}
	}



	/**
	 */
	protected function attachedToPresenter()
	{

	}

}
