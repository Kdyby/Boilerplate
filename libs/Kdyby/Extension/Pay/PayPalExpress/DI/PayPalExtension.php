<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Pay\PayPalExpress\DI;

use Kdyby;
use Nette;
use Nette\Utils\PhpGenerator\ClassType;
use Nette\Utils\Validators;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class PayPalExtension extends Nette\Config\CompilerExtension
{

	/**
	 * @var array
	 */
	public $defaults = array(
		'sandbox' => TRUE,
		'currency' => 'CZK',
	);



	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		Validators::assertField($config, 'account');
		Validators::assertField($config, 'username');
		Validators::assertField($config, 'password');
		Validators::assertField($config, 'signature');
		Validators::assertField($config, 'sandbox', 'bool');

		$client = $builder->addDefinition($this->prefix('client'))
			->setClass('Kdyby\Extension\Pay\PayPalExpress\PayPal')
			->setArguments(array($config))
			->addSetup('setCurrency', array($config['currency']));

		if ($config['sandbox'] === FALSE) {
			$client->addSetup('disableSandbox');
		}
	}



	/**
	 * @param \Nette\Utils\PhpGenerator\ClassType $class
	 */
	public function afterCompile(ClassType $class)
	{
		$container = $this->getContainerBuilder();
		$init = $class->methods['initialize'];
		/** @var \Nette\Utils\PhpGenerator\Method $init */

		$init->addBody($container->formatPhp(
			'Nette\Diagnostics\Debugger::$blueScreen->addPanel(?);',
			Nette\Config\Compiler::filterArguments(array(
				'Kdyby\Extension\Pay\PayPalExpress\Diagnostics\Panel::renderException'
			))
		));
	}



	/**
	 * @param \Nette\Config\Configurator $configurator
	 */
	public static function register(Nette\Config\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\Config\Compiler $compiler) {
			$compiler->addExtension('paypalExpress', new PayPalExtension());
		};
	}

}
