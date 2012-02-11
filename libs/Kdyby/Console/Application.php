<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Console;

use Kdyby;
use Nette;
use Nette\Diagnostics\Debugger;
use Symfony;
use Symfony\Component\Console;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Application extends Kdyby\Application\Application
{

	/** @var \Symfony\Component\Console\Input\ArgvInput */
	private $consoleInput;

	/** @var \Symfony\Component\Console\Output\ConsoleOutput */
	private $consoleOutput;



	/**
	 * @return integer
	 */
	public function run()
	{
		$this->consoleInput = new Console\Input\ArgvInput();
		$this->consoleOutput = new Console\Output\ConsoleOutput();

		// package errors should not be handled by console life-cycle
		$cli = $this->createApplication();

		$exitCode = 1;
		try {
			// run the console
			$exitCode = $cli->run($this->consoleInput, $this->consoleOutput);

		} catch (\Exception $e) {
			// fault barrier
			$this->onError($this, $e);

			if (!$this->catchExceptions) {
				$this->onShutdown($this, $e);

				// log
				Debugger::log($e, 'console');

				// render exception
				$cli->renderException($e, $this->consoleOutput);
				return $exitCode;
			}
		}

		$this->onShutdown($this, isset($e) ? $e : NULL);
		return $exitCode;
	}



	/**
	 * @return \Symfony\Component\Console\Application
	 */
	protected function createApplication()
	{
		$container = $this->getConfigurator()->getContainer();

		// create
		$cli = new Console\Application(
			Kdyby\Framework::NAME . " Command Line Interface",
			Kdyby\Framework::VERSION
		);

		// override error handling
		$cli->setCatchExceptions(FALSE);
		$cli->setAutoExit(FALSE);

		// set helpers
		$cli->setHelperSet($container->kdyby->{'console.helpers'});

		// register packages
		$this->getConfigurator()
			->getPackages()->registerCommands($cli);

		return $cli;
	}

}
