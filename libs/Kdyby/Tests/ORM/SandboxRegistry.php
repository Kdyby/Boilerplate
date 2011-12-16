<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\ORM;

use Doctrine;
use Doctrine\Common\DataFixtures;
use Kdyby;
use Kdyby\Tests\OrmTestCase;
use Nette;
use Symfony\Component\DependencyInjection\ContainerInterface;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SandboxRegistry extends Kdyby\Packages\DoctrinePackage\Registry
{
	/**
	 * @var \Kdyby\Tests\ORM\DataFixturesLoader[]
	 */
	private $fixtureLoaders;



	/**
	 * @param \Kdyby\Tests\OrmTestCase $testCase
	 */
	public function loadFixtures(OrmTestCase $testCase)
	{
		if ($this->fixtureLoaders === NULL) {
			$this->fixtureLoaders = array();

			foreach ($this->getEntityManagerNames() as $emName) {
				$this->fixtureLoaders[] = new DataFixturesLoader(
					$this->container->get($emName . '.data_fixtures.loader'),
					$this->container->get($emName . '.data_fixtures.executor')
				);
			}
		}

		foreach ($this->fixtureLoaders as $loader) {
			$loader->loadFixtures($testCase);
		}
	}

}
