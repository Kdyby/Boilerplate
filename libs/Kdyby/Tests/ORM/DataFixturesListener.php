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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DataFixturesListener extends Nette\Object implements Doctrine\Common\EventSubscriber
{

	/** @var DataFixtures\Loader */
	private $loader;

	/** @var DataFixtures\Executor\AbstractExecutor */
	private $executor;



	/**
	 * @param DataFixtures\Loader $loader
	 * @param DataFixtures\Executor\AbstractExecutor $executor
	 */
	public function __construct(DataFixtures\Loader $loader, DataFixtures\Executor\AbstractExecutor $executor)
	{
		$this->loader = $loader;
		$this->executor = $executor;
	}



	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			'loadFixtures'
		);
	}



	/**
	 * Appends Data Fixtures to current database DataSet
	 *
	 * @param EventArgs $eventArgs
	 */
	public function loadFixtures(EventArgs $eventArgs)
	{
		$this->addFixtureClasses($this->getTestFixtureClasses($eventArgs->getTestCase()));
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
	 * @param OrmTestCase $testCase
	 * @return array
	 */
	private function getTestFixtureClasses(OrmTestCase $testCase)
	{
		$method = $testCase->getReflection()->getMethod($testCase->getName(FALSE));
		$annotations = $method->getAnnotations();

		return array_map(function ($class) use ($method) {
			if (substr_count($class, '\\') !== 0) {
				return $class;
			}

			return $method->getDeclaringClass()->getNamespaceName() . '\\' .  $class;
		}, isset($annotations['Fixture']) ? $annotations['Fixture'] : array());
	}

}