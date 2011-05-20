<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;



/**
 * @author Patrik Votoček
 * @author Filip Procházka
 *
 * @property-read Nette\DI\Container $context
 */
class Application extends Nette\Application\Application
{

	public function run()
	{
		$this->context->freeze();

		if (PHP_SAPI == "cli") {
			return $this->context->console->run();
		}

		return parent::run();
	}

}