<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\Redis;

use Kdyby;
use Kdyby\Extension\Redis\RedisClient;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RedisClientTest extends AbstractCase
{

	/**
	 * @var string
	 */
	private $ns;



	protected function setUp()
	{
		parent::setUp();
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



	public function testLargeData()
	{
		$data = str_repeat('Kdyby', 1e6);
		$this->client->set('large', $data);
		$this->assertSame($data, $this->client->get('large'));
	}



	public function testNullReply()
	{
		$this->assertNull($this->client->get('nonexistingkey'));
	}



	public function testExec()
	{
		$this->assertEquals('1', $this->client->sadd('test:key', 'item1'));
		$this->assertEquals('1', $this->client->sadd('test:key', 'item2'));

		$this->assertEquals('OK', $this->client->multi());
		$this->assertEquals('QUEUED', $this->client->sMembers('test:key'));
		$this->assertEquals('QUEUED', $this->client->sMembers('test:key'));
		$this->assertEquals(array(array('item1', 'item2'), array('item1', 'item2')), $this->client->exec());
	}



	public function testExecWithClosure()
	{
		$this->assertEquals('1', $this->client->sadd('test:key', 'item1'));
		$this->assertEquals('1', $this->client->sadd('test:key', 'item2'));

		$result = $this->client->multi(function (RedisClient $client) {
			$client->sMembers('test:key');
			$client->sMembers('test:key');
		});

		$this->assertEquals(array(array('item1', 'item2'), array('item1', 'item2')), $result);
	}



	/**
	 * @expectedException Kdyby\Extension\Redis\TransactionException
	 */
	public function testExecException()
	{
		$other = new RedisClient();

		$this->client->set('foo', 1);
		$this->client->watch('foo');

		$this->client->multi();
		$other->del('foo');
		$this->client->incr('foo');
		$this->client->exec();
	}


}
