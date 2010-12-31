<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */



namespace Kdyby\Application;

use Nette;
use Nette\Environment;



final class Kdyby extends Nette\Application\Application
{
	/** @var string */
	public $errorPresenter = 'Error';



	/**
	 * @return Kdyby\Application\Kdyby
	 */
	public function registerPanels()
	{
		//NetteTranslator\Panel::register(Environment::getService('Nette\ITranslator'), \NetteTranslator\Panel::LAYOUT_VERTICAL);

		//Panel\UserPanel::register()
		//	->addCredentials('HosipLan', 'reddwarf')
		//	->setNameColumn('name');

		return $this;
	}

}