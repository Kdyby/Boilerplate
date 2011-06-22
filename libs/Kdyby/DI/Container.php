<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Symfony\Component\Console;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 *
 * @property-read Kdyby\Doctrine\ORM\Container $doctrine
 * @property-read Nette\Application\IRouter $router
 * @property-read Console\Helper\HelperSet $consoleHelpers
 * @property-read Kdyby\Tools\FreezableArray $consoleCommands
 * @property-read Console\Application $console
 * @property-read Nette\Application\Application $application
 * @property-read Nette\Application\IPresenterFactory $presenterFactory
 * @property-read Kdyby\Application\ModuleCascadeRegistry $moduleRegistry
 * @property-read Kdyby\Templates\ITemplateFactory $templateFactory
 * @property-read Nette\Latte\Engine $latteEngine
 * @property-read Nette\Http\Request $httpRequest
 * @property-read Nette\Http\Response $httpResponse
 * @property-read Nette\Http\Context $httpContext
 * @property-read Nette\Http\Session $session
 * @property-read Nette\Http\User $user
 * @property-read Kdyby\Security\Users $users
 * @property-read Nette\Caching\IStorage $cacheStorage
 * @property-read Nette\Caching\Storages\PhpFileStorage $templateCacheStorage
 * @property-read Nette\Caching\Storages\IJournal $cacheJournal
 * @property-read Nette\Mail\IMailer $mailer
 * @property-read Nette\Loaders\RobotLoader $robotLoader
 * @property-read Kdyby\Components\Grinder\GridFactory $grinderFactory
 * @property-read Kdyby\Application\RequestManager $requestManager
 * @property-read Kdyby\Modules\InstallWizard $installWizard
 */
class Container extends Nette\DI\Container
{

	/**
	 * @param string $key
	 * @param string|NULL $default
	 * @throws Nette\OutOfRangeException
	 * @return mixed
	 */
	public function getParam($key, $default = NULL)
	{
		if (isset($this->params[$key])) {
			return $this->params[$key];

		} elseif (func_num_args()>1) {
			return $default;
		}

		throw new Nette\OutOfRangeException("Missing key $key in " . get_class($this) . '->params');
	}



	/**
	 * @param string $name
	 * @param Nette\DI\IContainer $container
	 */
	public function lazyCopy($name, Nette\DI\IContainer $container)
	{
		$this->addService($name, function() use ($name, $container) {
			return $container->getService($name);
		});
	}

}