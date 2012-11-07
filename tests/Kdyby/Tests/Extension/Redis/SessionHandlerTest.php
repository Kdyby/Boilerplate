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
use Kdyby\Extension\Redis\RedisSessionHandler;
use Kdyby\Extension\Redis\RedisClient;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SessionHandlerTest extends Kdyby\Tests\TestCase
{

	/**
	 * @var \Kdyby\Extension\Redis\RedisClient
	 */
	private $client;



	/***/
	public function setUp()
	{
		$this->client = new RedisClient();
		try {
			$this->client->connect();

		} catch (Kdyby\Extension\Redis\RedisClientException $e) {
			$this->markTestSkipped($e->getMessage());
		}

		try {
			$this->client->assertVersion();

		} catch (Nette\Utils\AssertionException $e) {
			$this->markTestSkipped($e->getMessage());
		}

		$this->client->flushDb();
	}


	/**
	 * @group concurrency
	 */
	public function testConsistency()
	{
		$this->markTestSkipped('Todo: implement session locking purely in redis.');

		sleep(5);
		$client = new RedisClient();
		$tempDir = $this->tempDir();
		$userId = md5(1);

		$client->flushDb();

		$this->threadStress(function () use ($tempDir, $userId) {
			$handler = new RedisSessionHandler(new RedisClient(), $tempDir);

			// read
			$handler->open('path', 'session_id');
			$session = array('counter' => 0);
			if ($data = $handler->read($userId)) {
				$session = unserialize($data);
			}

			// modify
			$session['counter'] += 1;

			// write
			$handler->write($userId, serialize($session));
			$handler->close();
		});

		$handler = new RedisSessionHandler($client);
		$handler->open('path', 'session_id');

		$data = $handler->read($userId);
		$this->assertNotEmpty($data);

		$session = unserialize($data);
		$this->assertInternalType('array', $session);
		$this->assertArrayHasKey('counter', $session);
		$this->assertEquals(100, $session['counter']);
	}

}
