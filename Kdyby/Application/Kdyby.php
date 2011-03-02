<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */



namespace Kdyby\Application;

use Nette;
use Nette\Environment;



final class Kdyby extends Nette\Application\Application
{
	/** @var string */
	public $errorPresenter = 'Error';



	/**
	 * Dispatch a HTTP request to a front controller.
	 * @return void
	 */
	public function run()
	{
		$this->getContext()->freeze();

		parent::run();
	}



	/**
	 * @return Kdyby\Application\Kdyby
	 */
	public function registerPanels()
	{
		//NetteTranslator\Panel::register(Environment::getService('Nette\ITranslator'), \NetteTranslator\Panel::LAYOUT_VERTICAL);

		//Panel\UserPanel::register()
		//	->addCredentials('HosipLan', 'reddwarf')
		//	->setNameColumn('name');


		// develop environment!
		//\Kdyby\Debug\DoctrinePanel::register();

		return $this;
	}

}