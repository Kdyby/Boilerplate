<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Redis;

use Kdyby;
use Nette;



/**
 * @author Ondřej Nešpor
 * @author Filip Procházka <filip@prochazka.su>
 */
class ExclusiveLock extends Nette\Object
{

	/**
	 * @var RedisClient
	 */
	private $client;

	/**
	 * @var array
	 */
	private $keys = array();

	/**
	 * @var int
	 */
	public $duration = 15;



	/**
	 * @param RedisClient $redisClient
	 */
	public function __construct(RedisClient $redisClient)
	{
		$this->client = $redisClient;
	}



	/**
	 * @return int
	 */
	public function calculateTimeout()
	{
		return time() + abs((int)$this->duration) + 1;
	}



	/**
	 * Tries to acquire a key lock, otherwise waits until it's released and repeats.
	 *
	 * @param string $key
	 * @throws LockException
	 * @return bool
	 */
	public function acquireLock($key)
	{
		if (isset($this->keys[$key])) {
			return $this->increaseLockTimeout($key);
		}

		$lockKey = $this->formatLock($key);
		$maxAttempts = 10;
		do {
			do {
				if ($this->client->setNX($lockKey, $timeout = $this->calculateTimeout())) {
					$this->keys[$key] = $timeout;
					return true;
				}

				$lockExpiration = $this->client->get($lockKey);
			} while (empty($lockExpiration) || ($lockExpiration >= time() && !usleep(10000)));

			$oldExpiration = $this->client->getSet($lockKey, $timeout = $this->calculateTimeout());
			if ($oldExpiration === $lockExpiration) {
				$this->keys[$key] = $timeout;
				return true;
			}

		} while (--$maxAttempts > 0);

		throw new LockException("Lock couldn't be acquired. Concurrency is too high.");
	}



	/**
	 * @param string $key
	 * @param $key
	 */
	public function release($key)
	{
		if (!isset($this->keys[$key])) {
			return false;
		}

		if ($this->keys[$key] <= time()) {
			unset($this->keys[$key]);
			return false;
		}

		$this->client->del($this->formatLock($key));
		unset($this->keys[$key]);
		return true;
	}



	/**
	 * @param string $key
	 * @throws LockException
	 */
	public function increaseLockTimeout($key)
	{
		if (!isset($this->keys[$key])) {
			return false;
		}

		if ($this->keys[$key] <= time()) {
			throw new LockException("Process ran too long. Increase lock duration, or extend lock regularly.");
		}

		$oldTimeout = $this->client->getSet($this->formatLock($key), $timeout = $this->calculateTimeout());
		if ((int)$oldTimeout !== (int)$this->keys[$key]) {
			throw new LockException("Some rude client have messed up the lock duration.");
		}
		$this->keys[$key] = $timeout;
		return true;
	}



	/**
	 * Release all acquired locks.
	 */
	public function releaseAll()
	{
		foreach ((array)$this->keys as $key => $timeout) {
			$this->release($key);
		}
	}



	/**
	 * Updates the indexing locks timeout.
	 */
	public function increaseLocksTimeout()
	{
		foreach ($this->keys as $key => $timeout) {
			$this->increaseLockTimeout($key);
		}
	}



	/**
	 * @param string $key
	 * @return int
	 */
	public function getLockTimeout($key)
	{
		if (!isset($this->keys[$key])) {
			return 0;
		}

		return $this->keys[$key] - time();
	}



	/**
	 * @param string $key
	 * @return string
	 */
	protected function formatLock($key)
	{
		return $key . ':lock';
	}



	public function __destruct()
	{
		$this->releaseAll();
	}

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class LockException extends RedisClientException
{

}
