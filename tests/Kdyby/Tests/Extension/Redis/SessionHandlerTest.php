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
class SessionHandlerTest extends AbstractCase
{

	/**
	 * @group concurrency
	 */
	public function testConsistency()
	{
		// sleep(5);
		$userId = md5(1);

		$this->threadStress(function () use ($userId) {
			$handler = new RedisSessionHandler(new RedisClient());

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

		$handler = new RedisSessionHandler($this->client);
		$handler->open('path', 'session_id');

		$data = $handler->read($userId);
		$this->assertNotEmpty($data);

		$session = unserialize($data);
		$this->assertInternalType('array', $session);
		$this->assertArrayHasKey('counter', $session);
		$this->assertEquals(100, $session['counter']);
	}

}
