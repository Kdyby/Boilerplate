<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Http;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FakeSession extends Nette\Http\Session
{
	/** @var \Kdyby\Tests\Http\FakeSessionSection[] */
	private $sections = array();

	/** @var bool */
	private $started = FALSE;

	/** @var array */
	private $options = array();

	/** @var string */
	private $id;

	/** @var string */
	private $name = 'session_id';



	/**
	 * @param \Nette\Http\IRequest $request
	 * @param \Nette\Http\IResponse $response
	 */
	public function __construct(Nette\Http\IRequest $request, Nette\Http\IResponse $response)
	{
		$this->regenerateId();
	}



	/**
	 */
	public function start()
	{
		$this->started = TRUE;
	}



	/**
	 * @return bool
	 */
	public function isStarted()
	{
		return $this->started;
	}



	/**
	 */
	public function close()
	{
		$this->started = NULL;
	}



	/**
	 */
	public function destroy()
	{
		$this->sections = array();
		$this->close();
	}



	/**
	 * @return bool
	 */
	public function exists()
	{
		return TRUE;
	}



	/**
	 *
	 */
	public function regenerateId()
	{
		$this->id = md5((string)microtime(TRUE));
	}



	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}



	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param string $section
	 * @param string $class
	 *
	 * @return \Kdyby\Tests\Http\FakeSessionSection
	 */
	public function getSection($section, $class = 'Kdyby\Tests\Http\FakeSessionSection')
	{
		return $this->sections[$section] = new $class($this, $section);
	}



	/**
	 * @deprecated
	 * @param $section
	 * @throws \Kdyby\NotSupportedException
	 */
	public function getNamespace($section)
	{
		throw new Kdyby\NotSupportedException;
	}



	/**
	 * @param string $section
	 *
	 * @return bool
	 */
	public function hasSection($section)
	{
		return isset($this->sections[$section]);
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->sections);
	}



	/**
	 */
	public function clean()
	{

	}



	/**
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		$this->options = $options + $this->options;
	}



	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}



	/**
	 * @param int|string $time
	 */
	public function setExpiration($time)
	{

	}



	/**
	 * @return array
	 */
	public function getCookieParameters()
	{
		$keys = array('cookie_path', 'cookie_domain', 'cookie_secure');
		$empty = array_fill_keys($keys, NULL);

		return array_intersect_key($this->options, $empty) + $empty;
	}



	/**
	 * @param \Nette\Http\ISessionStorage $storage
	 */
	public function setStorage(Nette\Http\ISessionStorage $storage)
	{

	}

}
