<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\QrEncode\DI;

use Kdyby;
use Nette;
use Nette\Utils\Validators;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class QrExtension extends Nette\Config\CompilerExtension
{

	/**
	 * @var array
	 */
	public $defaults = array(
		'size' => 4,
		'errorCorrection' => NULL,
		'version' => NULL,
		'margin' => 1,
		'options' => 0,
		'provider' => 'http://qre.kdyby.org',
		'apiKey' => NULL,
	);



	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$builder->addDefinition($this->prefix('config'))
			->setClass('Kdyby\Extension\QrEncode\DI\Configuration')
			->addSetup('$size', array($config['size']))
			->addSetup('$errorCorrection', array($config['errorCorrection']))
			->addSetup('$version', array($config['version']))
			->addSetup('$margin', array($config['margin']))
			->addSetup('$options', array($config['options']))
			->addSetup('$provider', array($config['provider']))
			->addSetup('$apiKey', array($config['apiKey']));

		$generator = $builder->addDefinition($this->prefix('generator'))
			->setClass('Kdyby\Extension\QrEncode\IGenerator');

		if (!empty($config['apiKey'])) {
			$generator->setFactory('Kdyby\Extension\QrEncode\QrRemoteGenerator');

		} else {
			$generator->setFactory('Kdyby\Extension\QrEncode\QrGenerator');
		}
	}



	public function beforeCompile()
	{
		$config = $this->getConfig($this->defaults);
		if (!empty($config['apiKey'])) {
			$configuration = new Configuration();
			$configuration->apiKey = $config['apiKey'];
			$configuration->provider = $config['provider'];

			if ($configuration->testConnection() === FALSE) {
				trigger_error("The QrCode generator is badly configured or components are missing. Please check log.", E_USER_WARNING);
			}
		}
	}



	/**
	 * @param \Nette\Config\Configurator $configurator
	 */
	public static function register(Nette\Config\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\Config\Compiler $compiler) {
			$compiler->addExtension('qrCode', new QrExtension());
		};
	}

}

