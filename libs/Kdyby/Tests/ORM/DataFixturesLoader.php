<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\ORM;

use Doctrine;
use Doctrine\Common\DataFixtures;
use Kdyby;
use Kdyby\Tests\OrmTestCase;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class DataFixturesLoader extends Nette\Object
{

	/** @var \Doctrine\Common\DataFixtures\Loader */
	private $loader;

	/** @var \Doctrine\Common\DataFixtures\Executor\AbstractExecutor */
	private $executor;



	/**
	 * @param \Doctrine\Common\DataFixtures\Loader $loader
	 * @param \Doctrine\Common\DataFixtures\Executor\AbstractExecutor $executor
	 */
	public function __construct(DataFixtures\Loader $loader, DataFixtures\Executor\AbstractExecutor $executor)
	{
		$this->loader = $loader;
		$this->executor = $executor;
	}



	/**
	 * Appends Data Fixtures to current database DataSet
	 *
	 * @param \Kdyby\Tests\OrmTestCase $testCase
	 */
	public function loadFixtures(OrmTestCase $testCase)
	{
		$this->addFixtureClasses($this->getTestFixtureClasses($testCase));
		$this->executor->execute($this->loader->getFixtures(), TRUE);
	}



	/**
	 * @param array $classes
	 * @param array $visited
	 */
	private function addFixtureClasses(array $classes, &$visited = array())
	{
		if (!$classes) {
			return;
		}

		$fixtures = array();
		foreach ($classes as $class) {
			if (in_array($class, $visited)) {
				continue;
			}

			$fixtures[] = $fixture = new $class;
			$this->loader->addFixture($fixture);
			$visited[] = $class;
		}

		foreach ($fixtures as $fixture) {
			if (!$fixture instanceof DataFixtures\DependentFixtureInterface) {
				continue;
			}

			$this->addFixtureClasses(array_diff($fixture->getDependencies(), $visited), $visited);
		}
	}



	/**
	 * @param \Kdyby\Tests\OrmTestCase $testCase
	 * @return array
	 */
	private function getTestFixtureClasses(OrmTestCase $testCase)
	{
		$method = $testCase->getReflection()->getMethod($testCase->getName(FALSE));
		$annotations = $method->getAnnotations();

		return array_map(function ($class) use ($method) {
			if (class_exists($class)) {
				return $class;
			}

			$testCaseNs = $method->getDeclaringClass()->getNamespaceName();
			if (class_exists($prefixed = $testCaseNs . '\\' . $class)) {
				return $prefixed;
			}

			if (class_exists($prefixed = $testCaseNs . '\\Fixture\\' . $class)) {
				return $prefixed;
			}

			throw new Kdyby\InvalidStateException("Fixtures $class for test " . $method . " could not be loaded.");
		}, isset($annotations['Fixture']) ? $annotations['Fixture'] : array());
	}

}
