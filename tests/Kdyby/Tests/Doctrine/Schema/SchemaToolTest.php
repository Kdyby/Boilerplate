<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Doctrine\Schema;

use Doctrine;
use Kdyby;
use Kdyby\Doctrine\Schema;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SchemaToolTest extends Kdyby\Tests\OrmTestCase
{

	/**
	 * @var \Kdyby\Tests\Doctrine\Schema\TestSubscriber
	 */
	private $subscriber;

	/**
	 * @var \Kdyby\Doctrine\Schema\SchemaTool
	 */
	private $schemaTool;



	public function setUp()
	{
		$this->createOrmSandbox(array(
			'Kdyby\Migrations\MigrationLog' // not important
		));

		$em = $this->getEntityManager();
		$evm = $em->getEventManager();

		// hack to platform, that supports table alters (Fuck you sqlite! Fuck you!)
		$conn = $em->getConnection();
		$platformRefl = Nette\Reflection\ClassType::from($conn)->getProperty('_platform');
		$platformRefl->setAccessible(TRUE);

		$mysqlPlatform = new \Doctrine\DBAL\Platforms\MySqlPlatform();
		$mysqlPlatform->setEventManager($evm);
		$platformRefl->setValue($conn, $mysqlPlatform);

		// create schema tool & subscriber
		$this->schemaTool = new Schema\SchemaTool($em);
		$this->subscriber = new TestSubscriber();
		$evm->addEventSubscriber($this->subscriber);
	}



	/**
	 * @return array
	 */
	public function dataListeners()
	{
		return array(
			array(Schema\SchemaTool::onCreateSchemaSql, 'Kdyby\Doctrine\Schema\CreateSchemaSqlEventArgs', 'getCreateSchemaSql'),
			array(Schema\SchemaTool::onDropDatabaseSql, 'Kdyby\Doctrine\Schema\DropDatabaseSqlEventArgs', 'getDropDatabaseSql'),
			array(Schema\SchemaTool::onDropSchemaSql, 'Kdyby\Doctrine\Schema\DropSchemaSqlEventArgs', 'getDropSchemaSql'),
			array(Schema\SchemaTool::onUpdateSchemaSql, 'Kdyby\Doctrine\Schema\UpdateSchemaSqlEventArgs', 'getUpdateSchemaSql'),
		);
	}



	/**
	 * @dataProvider dataListeners
	 *
	 * @param string $eventType
	 * @param string $eventClass
	 * @param string $method
	 */
	public function testListener($eventType, $eventClass, $method)
	{
		$test = $this;
		$invoker =& $this->subscriber->invokers[$eventType];
		$invoker = function ($eventArgs) use ($test, $eventClass) {
			/** @var \Kdyby\Tests\OrmTestCase $test */
			$test->assertInstanceOf($eventClass, $eventArgs);

			// modify sqls
			$sqls = $eventArgs->getSqls();
			$sqls[] = 'I WAS HERE, FANTOMAS;';
			$eventArgs->setSqls($sqls);
		};

		$classes = $this->getEntityManager()->getMetadataFactory()->getAllMetadata();
		$sqls = $this->schemaTool->$method($classes);
		$this->assertContains('I WAS HERE, FANTOMAS;', $sqls);
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TestSubscriber extends Nette\Object implements Doctrine\Common\EventSubscriber
{

	/**
	 * @var array|\Nette\Callback[]
	 */
	public $invokers = array();



	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Schema\SchemaTool::onCreateSchemaSql,
			Schema\SchemaTool::onDropDatabaseSql,
			Schema\SchemaTool::onDropSchemaSql,
			Schema\SchemaTool::onUpdateSchemaSql,
		);
	}



	/**
	 * @param string $name
	 * @param array $args
	 *
	 * @throws \PHPUnit_Framework_AssertionFailedError
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		if (!isset($this->invokers[$name])) {
			throw new \PHPUnit_Framework_AssertionFailedError(
				"Unexpected invocation " . get_called_class() . "::$name()."
			);
		}

		$invoker = callback($this->invokers[$name]);
		return $invoker->invokeArgs($args);
	}

}
