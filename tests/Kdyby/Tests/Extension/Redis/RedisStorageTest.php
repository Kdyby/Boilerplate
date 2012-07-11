<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests;

use Kdyby;
use Kdyby\Extension\Redis\RedisClient;
use Kdyby\Extension\Redis\RedisStorage;
use Nette;
use Nette\Caching\Cache;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RedisStorageTest extends Kdyby\Tests\TestCase
{


	/**
	 * @var \Kdyby\Extension\Redis\RedisClient
	 */
	private $client;

	/**
	 * @var \Kdyby\Extension\Redis\RedisStorage
	 */
	private $storage;



	protected function setUp()
	{
		$this->client = new RedisClient();
		try {
			$this->client->connect();
			$this->storage = new RedisStorage($this->client);

		} catch (Kdyby\Extension\Redis\RedisClientException $e) {
			$this->markTestSkipped($e->getMessage());
		}

		$this->client->flushDb();
	}



	/**
	 * key and data with special chars
	 *
	 * @return array
	 */
	public function basicData()
	{
		return array(
			$key = array(1, TRUE),
			$value = range("\x00", "\xFF"),
		);
	}



	public function testBasics()
	{
		list($key, $value) = $this->basicData();

		$cache = new Cache($this->storage);
		$this->assertFalse(isset($cache[$key]), "Is cached?");
		$this->assertNull($cache[$key], "Cache content");

		// Writing cache...
		$cache[$key] = $value;
		$this->assertTrue(isset($cache[$key]), "Is cached?");
		$this->assertSame($value, $cache[$key], "Is cache ok?");

		// Removing from cache using unset()...
		unset($cache[$key]);
		$this->assertFalse(isset($cache[$key]), "Is cached?");

		// Removing from cache using set NULL...
		$cache[$key] = $value;
		$cache[$key] = NULL;
		$this->assertFalse(isset($cache[$key]), "Is cached?");

		// Writing cache...
		$cache->save($key, $value);
		$this->assertSame($value, $cache->load($key), "Is cache ok?");
	}

}
