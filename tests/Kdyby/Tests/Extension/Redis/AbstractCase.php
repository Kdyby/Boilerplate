<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\Redis;

use Kdyby;
use Nette\Utils\AssertionException;
use Kdyby\Extension\Redis\RedisClientException;
use Kdyby\Extension\Redis\RedisClient;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class AbstractCase extends Kdyby\Tests\TestCase
{

	/**
	 * @var \Kdyby\Extension\Redis\RedisClient
	 */
	protected $client;



	protected function setUp()
	{
		$this->client = new RedisClient();
		try {
			$this->client->connect();

		} catch (RedisClientException $e) {
			$this->markTestSkipped($e->getMessage());
		}

		try {
			$this->client->assertVersion();

		} catch (AssertionException $e) {
			$this->markTestSkipped($e->getMessage());
		}

		$this->client->flushDb();
	}

}
