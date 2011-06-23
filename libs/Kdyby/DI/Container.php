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
use Nette\Utils\Strings;



/**
 * @author Filip Procházka
 *
 * @property-read Kdyby\Doctrine\Workspace $workspace
 * @property-read Kdyby\Doctrine\ORM\Container $sqldb
 * @property-read Kdyby\Doctrine\ODM\Container $couchdb
 *
 * @property-read Console\Helper\HelperSet $consoleHelpers
 * @property-read Kdyby\Tools\FreezableArray $consoleCommands
 * @property-read Console\Application $console
 *
 * @property-read Nette\Application\Application $application
 * @property-read Nette\Application\IPresenterFactory $presenterFactory
 * @property-read Kdyby\Application\ModuleCascadeRegistry $moduleRegistry
 * @property-read Kdyby\Application\RequestManager $requestManager
 *
 * @property-read Nette\Application\IRouter $router
 * @property-read Nette\Http\Request $httpRequest
 * @property-read Nette\Http\Response $httpResponse
 * @property-read Nette\Http\Context $httpContext
 * @property-read Nette\Http\Session $session
 *
 * @property-read Nette\Http\User $user
 * @property-read Kdyby\Security\Users $users
 *
 * @property-read Kdyby\Templates\ITemplateFactory $templateFactory
 * @property-read Nette\Caching\Storages\PhpFileStorage $templateCacheStorage
 * @property-read Nette\Latte\Engine $latteEngine
 *
 * @property-read Nette\Loaders\RobotLoader $robotLoader
 *
 * @property-read Nette\Caching\IStorage $cacheStorage
 * @property-read Nette\Caching\Storages\IJournal $cacheJournal
 *
 * @property-read Nette\Mail\IMailer $mailer
 *
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



	/********************* tools *********************/



	/**
	 * Expands %placeholders% in string.
	 * @param  string
	 * @return string
	 * @throws Nette\InvalidStateException
	 */
	public function expand($s)
	{
		if (is_string($s) && strpos($s, '%') !== FALSE) {
			$params = array_map(function ($arr) {return $arr[0];}, Nette\Utils\Strings::matchAll($s, '#(%[a-z0-9._-]*%)#i'));
			foreach ($params as $name) {
				$param = trim($name, '%');
				if ($param === '') {
					$val = '%';
				} else {
					$val = $this->params;
					foreach (explode('.', $param) as $key) {
						if (is_object($val) && property_exists($val, $key)) {
							$val = $val->$key;
						} elseif (is_array($val) && array_key_exists($key, $val)) {
							$val = $val[$key];
						} else {
							throw new Nette\InvalidArgumentException("Missing parameter '$param'.");
						}
					}
				}

				if (!is_scalar($val)) {
					if ($s === $name) {
						return $val;
					} else {
						throw new Nette\InvalidStateException("Unable to concatenate non-scalar parameter '$param' with a string or another parameter.");
					}
				}
				$values[] = $val;
			}
			$s = str_replace($params, $values, $s);
		}
		return $s;
	}

}