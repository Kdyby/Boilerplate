<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Social\Facebook\DI;

use Nette;
use Nette\Utils\Validators;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FacebookExtension extends Nette\Config\CompilerExtension
{

	/**
	 * @var array
	 */
	public $defaults = array(
		'appId' => NULL,
		'appSecret' => NULL,
		'fileUploadSupport' => FALSE,
		'trustForwarded' => FALSE,
		'domains' => array(),
		'permissions' => array(),
	);



	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$config = $this->getConfig($this->defaults);
		Validators::assert($config['appId'], 'number', 'Application ID');
		Validators::assert($config['appSecret'], 'string:32', "Application secret");
		Validators::assert($config['fileUploadSupport'], 'bool', "file upload support");
		Validators::assert($config['trustForwarded'], 'bool', "trust forwarded");
		Validators::assert($config['domains'], 'array', "api domains");
		Validators::assert($config['permissions'], 'list', "permissions scope");

		$configurator = $builder->addDefinition($this->prefix('config'))
			->setClass('Kdyby\Extension\Social\Facebook\Configuration')
			->setArguments(array($config['appId'], $config['appSecret']))
			->addSetup('$fileUploadSupport', array($config['fileUploadSupport']))
			->addSetup('$trustForwarded', array($config['trustForwarded']))
			->addSetup('$permissions', array($config['permissions']))
			->setInternal(TRUE)
			->setInject(FALSE);

		if ($config['domains']) {
			$configurator->addSetup('$service->domains = ? + $service->domains', array($config['domains']));
		}

		$builder->addDefinition($this->prefix('session'))
			->setClass('Kdyby\Extension\Social\Facebook\SessionStorage')
			->setInternal(TRUE)
			->setInject(FALSE);

		$apiClient = $builder->addDefinition($this->prefix('apiClient'))
			->setFactory('Kdyby\Extension\Social\Facebook\Api\CurlClient')
			->setClass('Kdyby\Extension\Social\Facebook\ApiClient')
			->setInternal(TRUE);

		if ($builder->parameters['debugMode']) {
			$builder->addDefinition($this->prefix('panel'))
				->setClass('Kdyby\Extension\Social\Facebook\Diagnostics\Panel')
				->setInternal(TRUE)
				->setInject(FALSE);

			$apiClient->addSetup($this->prefix('@panel') . '::register', array('@self'));
		}

		$builder->addDefinition($this->prefix('client'))
			->setClass('Kdyby\Extension\Social\Facebook\Facebook')
			->setInject(FALSE);
	}



	/**
	 * @param \Nette\Config\Configurator $configurator
	 */
	public static function register(Nette\Config\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\Config\Compiler $compiler) {
			$compiler->addExtension('facebook', new FacebookExtension());
		};
	}

}
