<?php

namespace Kdyby\Extension\Social\Facebook\DI;

use Nette;
use Nette\Utils\Validators;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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

		$configurator = $builder->addDefinition($this->prefix('config'))
			->setClass('Kdyby\Extension\Social\Facebook\Configuration')
			->setArguments(array($config['appId'], $config['appSecret']))
			->setInternal(TRUE);

		if ($config['domains']) {
			$configurator->addSetup('$service->domains = ? + $service->domains', array($config['domains']));
		}

		$builder->addDefinition($this->prefix('session'))
			->setClass('Kdyby\Extension\Social\Facebook\SessionStorage')
			->setInternal(TRUE);

		$apiClient = $builder->addDefinition($this->prefix('apiClient'))
			->setFactory('Kdyby\Extension\Social\Facebook\Api\CurlClient')
			->setClass('Kdyby\Extension\Social\Facebook\ApiClient')
			->setInternal(TRUE);

		if ($builder->parameters['debugMode']) {
			$builder->addDefinition($this->prefix('panel'))
				->setClass('Kdyby\Extension\Social\Facebook\Diagnostics\Panel')
				->addSetup('register')
				->setInternal(TRUE);
			$apiClient->addSetup('injectPanel', array($this->prefix('@panel')));
		}

		$builder->addDefinition($this->prefix('client'))
			->setClass('Kdyby\Extension\Social\Facebook\Facebook')
			->addSetup('?->injectFacebook(?)', array($this->prefix('@apiClient'), '@self'));
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
