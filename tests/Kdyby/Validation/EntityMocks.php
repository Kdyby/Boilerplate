<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Validation;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class IdentityMock extends Nette\Object
{

	private $username;

	private $name;

	private $surname;

	private $email;

	private $info;

	private $gallery = array();


	public function __construct($values)
	{
		foreach ($values as $key => $value) {
			$this->$key = $value;
		}
	}


	public function &__get($name)
	{
		return isset($this->$name) ? $this->$name : NULL;
	}

}



class IdentityInfoMock extends Nette\Object
{

	private $phone;

	private $data = array();


	public function __construct($values)
	{
		foreach ($values as $key => $value) {
			$this->$key = $value;
		}
	}


	public function &__get($name)
	{
		return isset($this->$name) ? $this->$name : NULL;
	}

}



class ImageMock extends Nette\Object
{

	private $name;

	private $file;


	public function __construct($values)
	{
		foreach ($values as $key => $value) {
			$this->$key = $value;
		}
	}


	public function &__get($name)
	{
		return isset($this->$name) ? $this->$name : NULL;
	}

}