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
use Kdyby\Extension\Redis\RedisClient;
use Kdyby\Extension\Redis\ExclusiveLock;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ExclusiveLockTest extends AbstractCase
{


	/**
	 * @expectedException \Kdyby\Extension\Redis\LockException
	 * @expectedExceptionMessage Process ran too long. Increase lock duration, or extend lock regularly.
	 */
	public function testLockExpired()
	{
		$first = new ExclusiveLock($this->client);
		$first->duration = 1;

		$this->assertTrue($first->acquireLock('foo:bar'));
		sleep(3);

		$first->increaseLockTimeout('foo:bar');
	}


	public function testDeadlockHandling()
	{
		$first = new ExclusiveLock($this->client);
		$first->duration = 1;
		$second = new ExclusiveLock(new RedisClient());
		$second->duration = 1;

		$this->assertTrue($first->acquireLock('foo:bar'));
		sleep(3); // first died?

		$this->assertTrue($second->acquireLock('foo:bar'));
	}

}
