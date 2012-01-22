<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations\Console;

use Kdyby;
use Nette;
use Symfony;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;



/**
 * Command for generating new migration classes
 *
 * @todo: automatically open in IDE
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class GenerateCommand extends Symfony\Component\Console\Command\Command
{

	/**
	 */
	protected function configure()
	{
        $this
			->setName('kdyby:generate:migrations')
			->setDescription('Generate a blank migration class.')
			->addArgument('package', InputArgument::REQUIRED);
	}

}
