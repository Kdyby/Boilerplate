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
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RedisClientTest extends Kdyby\Tests\TestCase
{

	/**
	 * @var \Kdyby\Extension\Redis\RedisClient
	 */
	private $client;

	/**
	 * @var string
	 */
	private $ns;


	protected function setUp()
	{
		$this->client = new RedisClient();
		try {
			$this->client->connect();

		} catch (Kdyby\Extension\Redis\RedisClientException $e) {
			$this->markTestSkipped($e->getMessage());
		}

		$this->ns = Nette\Utils\Strings::random();
	}



	public function testPrimitives()
	{
		$secret = "I'm batman";
		$key = $this->ns . 'redis-test-secred';

		$this->client->set($key, $secret);
		$this->client->expire($key, 10);

		$this->assertSame($secret, $this->client->get($key));
	}

}
