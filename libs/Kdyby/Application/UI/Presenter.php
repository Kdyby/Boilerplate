<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application\UI;

use Nette;
use Kdyby;
use Kdyby\Application\Presentation\Bundle;



/**
 * @author Filip Procházka
 *
 * @property Kdyby\DependencyInjection\ServiceContainer $serviceContainer
 * @property Bundle $applicationBundle
 */
class Presenter extends Nette\Application\UI\Presenter
{

	/** @persistent */
	public $language = 'cs';

	/** @persistent */
	public $backlink;



	public function __construct()
	{
		parent::__construct(NULL, NULL);
	}



	/**
	 * If Debugger is enabled, print template variables to debug bar
	 */
	protected function afterRender()
	{
		parent::afterRender();

		if (Nette\Diagnostics\Debugger::isEnabled()) { // todo: as panel
			Nette\Diagnostics\Debugger::barDump($this->template->getParams(), 'Template variables');
		}
	}

}