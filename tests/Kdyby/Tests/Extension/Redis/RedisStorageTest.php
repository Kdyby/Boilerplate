<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests;

use Kdyby;
use Kdyby\Extension\Redis\RedisClient;
use Kdyby\Extension\Redis\RedisStorage;
use Kdyby\Extension\Redis\RedisJournal;
use Nette;
use Nette\Caching\Cache;



/**
 * @author Filip Procházka <filip@prochazka.su>
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



	/**
	 * @param mixed $val
	 * @return mixed
	 */
	public static function dependency($val)
	{
		return $val;
	}



	public function testCallbacks()
	{
		$key = 'nette';
		$value = 'rulez';

		$cache = new Cache($this->storage);
		$cb = get_called_class() . '::dependency';

		// Writing cache...
		$cache->save($key, $value, array(
			Cache::CALLBACKS => array(array($cb, 1)),
		));

		$this->assertTrue(isset($cache[$key]), 'Is cached?');

		// Writing cache...
		$cache->save($key, $value, array(
			Cache::CALLBACKS => array(array($cb, 0)),
		));

		$this->assertFalse(isset($cache[$key]), 'Is cached?');
	}



	public function testCleanAll()
	{
		$cacheA = new Cache($this->storage);
		$cacheB = new Cache($this->storage, 'B');

		$cacheA['test1'] = 'David';
		$cacheA['test2'] = 'Grudl';
		$cacheB['test1'] = 'divaD';
		$cacheB['test2'] = 'ldurG';

		$this->assertSame('David Grudl divaD ldurG', implode(' ', array(
			$cacheA['test1'],
			$cacheA['test2'],
			$cacheB['test1'],
			$cacheB['test2'],
		)));

		$this->storage->clean(array(Cache::ALL => TRUE));

		$this->assertNull($cacheA['test1']);
		$this->assertNull($cacheA['test2']);
		$this->assertNull($cacheB['test1']);
		$this->assertNull($cacheB['test2']);
	}



	public function testExpiration()
	{
		$key = 'nette';
		$value = 'rulez';

		$cache = new Cache($this->storage);

		// Writing cache...
		$cache->save($key, $value, array(
			Cache::EXPIRATION => time() + 3,
		));

		// Sleeping 1 second
		sleep(1);
		$this->assertTrue(isset($cache[$key]), 'Is cached?');

		// Sleeping 3 seconds
		sleep(3);
		$this->assertFalse(isset($cache[$key]), 'Is cached?');
	}



	public function testIntKeys()
	{
		// key and data with special chars
		$key = 0;
		$value = range("\x00", "\xFF");

		$cache = new Cache($this->storage);
		$this->assertFalse(isset($cache[$key]), 'Is cached?');
		$this->assertNull($cache[$key], 'Cache content');

		// Writing cache...
		$cache[$key] = $value;
		$this->assertTrue(isset($cache[$key]), 'Is cached?');
		$this->assertSame($value, $cache[$key], 'Is cache ok?');

		// Removing from cache using unset()...
		unset($cache[$key]);
		$this->assertFalse(isset($cache[$key]), 'Is cached?');

		// Removing from cache using set NULL...
		$cache[$key] = $value;
		$cache[$key] = NULL;
		$this->assertFalse(isset($cache[$key]), 'Is cached?');
	}



	public function testDependentItems()
	{
		$key = 'nette';
		$value = 'rulez';

		$cache = new Cache($this->storage);

		// Writing cache...
		$cache->save($key, $value, array(
			Cache::ITEMS => array('dependent'),
		));
		$this->assertTrue(isset($cache[$key]), 'Is cached?');

		// Modifing dependent cached item
		$cache['dependent'] = 'hello world';
		$this->assertFalse(isset($cache[$key]), 'Is cached?');

		// Writing cache...
		$cache->save($key, $value, array(
			Cache::ITEMS => 'dependent',
		));
		$this->assertTrue(isset($cache[$key]), 'Is cached?');

		// Modifing dependent cached item
		sleep(2);
		$cache['dependent'] = 'hello europe';
		$this->assertFalse(isset($cache[$key]), 'Is cached?');

		// Writing cache...
		$cache->save($key, $value, array(
			Cache::ITEMS => 'dependent',
		));
		$this->assertTrue(isset($cache[$key]), 'Is cached?');

		// Deleting dependent cached item
		$cache['dependent'] = NULL;
		$this->assertFalse(isset($cache[$key]), 'Is cached?');
	}



	/**
	 */
	public function testLoadOrSave()
	{
		// key and data with special chars
		$key = '../' . implode('', range("\x00", "\x1F"));
		$value = range("\x00", "\xFF");

		$cache = new Cache($this->storage);
		$this->assertFalse(isset($cache[$key]), 'Is cached?');

		// Writing cache using Closure...
		$res = $cache->load($key, function(& $dp) use ($value) {
			$dp = array(
				Cache::EXPIRATION => time() + 2,
			);
			return $value;
		});

		$this->assertSame($value, $res, 'Is result ok?');
		$this->assertSame($value, $cache->load($key), 'Is cache ok?');

		// Sleeping 3 seconds
		sleep(3);
		$this->assertFalse(isset($cache[$key]), 'Is cached?');
	}



	public function testNamespace()
	{
		$cacheA = new Cache($this->storage, 'a');
		$cacheB = new Cache($this->storage, 'b');

		// Writing cache...
		$cacheA['key'] = 'hello';
		$cacheB['key'] = 'world';

		$this->assertTrue(isset($cacheA['key']), 'Is cached #1?');
		$this->assertTrue(isset($cacheB['key']), 'Is cached #2?');
		$this->assertSame('hello', $cacheA['key'], 'Is cache ok #1?');
		$this->assertSame('world', $cacheB['key'], 'Is cache ok #2?');

		// Removing from cache #2 using unset()...
		unset($cacheB['key']);
		$this->assertTrue(isset($cacheA['key']), 'Is cached #1?');
		$this->assertFalse(isset($cacheB['key']), 'Is cached #2?');
	}



	public function testPriority()
	{
		$storage = new RedisStorage($this->client, new RedisJournal($this->client));
		$cache = new Cache($storage);

		// Writing cache...
		$cache->save('key1', 'value1', array(
			Cache::PRIORITY => 100,
		));
		$cache->save('key2', 'value2', array(
			Cache::PRIORITY => 200,
		));
		$cache->save('key3', 'value3', array(
			Cache::PRIORITY => 300,
		));
		$cache['key4'] = 'value4';

		// Cleaning by priority...
		$cache->clean(array(
			Cache::PRIORITY => '200',
		));

		$this->assertFalse(isset($cache['key1']), 'Is cached key1?');
		$this->assertFalse(isset($cache['key2']), 'Is cached key2?');
		$this->assertTrue(isset($cache['key3']), 'Is cached key3?');
		$this->assertTrue(isset($cache['key4']), 'Is cached key4?');
	}



	public function testTags()
	{
		$storage = new RedisStorage($this->client, new RedisJournal($this->client));
		$cache = new Cache($storage);

		// Writing cache...
		$cache->save('key1', 'value1', array(
			Cache::TAGS => array('one', 'two'),
		));
		$cache->save('key2', 'value2', array(
			Cache::TAGS => array('one', 'three'),
		));
		$cache->save('key3', 'value3', array(
			Cache::TAGS => array('two', 'three'),
		));
		$cache['key4'] = 'value4';

		// Cleaning by tags...
		$cache->clean(array(
			Cache::TAGS => 'one',
		));

		$this->assertFalse(isset($cache['key1']), 'Is cached key1?');
		$this->assertFalse(isset($cache['key2']), 'Is cached key2?');
		$this->assertTrue(isset($cache['key3']), 'Is cached key3?');
		$this->assertTrue(isset($cache['key4']), 'Is cached key4?');
	}

}
