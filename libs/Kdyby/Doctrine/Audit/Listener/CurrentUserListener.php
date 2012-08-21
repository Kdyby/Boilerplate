<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit\Listener;

use Doctrine;
use Doctrine\DBAL\Platforms;
use Doctrine\DBAL\Events as DbalEvents;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Kdyby;
use Kdyby\Doctrine\Audit\AuditConfiguration;
use Kdyby\Security\User;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CurrentUserListener extends Nette\Object implements Kdyby\Extension\EventDispatcher\EventSubscriber
{

	/**
	 * @var \Kdyby\Security\User
	 */
	private $user;

	/**
	 * @var \Kdyby\Doctrine\Audit\AuditConfiguration
	 */
	private $config;



	/**
	 * @param \Kdyby\Doctrine\Audit\AuditConfiguration $config
	 * @param \Kdyby\Security\User $user
	 */
	public function __construct(AuditConfiguration $config, User $user)
	{
		$this->user = $user;
		$this->config = $config;
	}



	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			DbalEvents::postConnect
		);
	}



	/**
	 * @param \Doctrine\DBAL\Event\ConnectionEventArgs $args
	 *
	 * @throws \Kdyby\NotSupportedException
	 */
	public function postConnect(ConnectionEventArgs $args)
	{
		// set current user to configuration
		$this->config->setCurrentUser($this->user->getId());

		$conn = $args->getConnection();
		if ($conn->getDatabasePlatform() instanceof Platforms\MySqlPlatform) {
			$variableSql = 'SET @kdyby_current_user = ?';

		} elseif ($conn->getDatabasePlatform() instanceof Platforms\SqlitePlatform) {
			/** @var \Doctrine\DBAL\Schema\SqliteSchemaManager $sm */
			$sm = $conn->getSchemaManager();
			if (!$sm->tablesExist('db_session_variables')) {
				$conn->exec('CREATE TEMPORARY TABLE db_session_variables (name TEXT, value TEXT)');
			}

			$variableSql = "INSERT INTO db_session_variables (name, value) VALUES ('kdyby_current_user', ?)";

		} else {
			throw new Kdyby\NotSupportedException("Sorry, but your platform is not supported.");
		}

		// pass current user to database
		$conn->executeQuery($variableSql, array(
			$this->config->getCurrentUser()
		));
	}

}
