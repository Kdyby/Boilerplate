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


namespace Kdyby\AdminModule;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class BasePresenter extends Kdyby\Application\CmsPresenter
{

	// security and stuff
	protected function startup()
	{
		parent::startup();

		// theme
		$this->user->theme->switchTheme(Nette\Environment::expand('%wwwDir%/_kdyby-admin'));

		// admin bundle
		$bundleRepo = $this->serviceContainer->entityManager->getRepository('Kdyby\Application\Presentation\Bundle');
		$this->applicationBundle = $bundleRepo->findOneByPlaceholderName(BundleInfo::PLACEHOLDER_NAME);
	}

}