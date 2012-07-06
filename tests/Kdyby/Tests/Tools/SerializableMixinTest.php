<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Kdyby\Tools\SerializableMixin;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SerializableMixinTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return SexyEntity
	 */
	public function data()
	{
		$entity = new SexyEntity(1, 'sexy', 'very', 'bzzzz');
		$entity->foo = 'bar';
		$entity->bar = 'yes please';
		$entity->lorem = 'ipsum';
		return $entity;
	}



	public function testFunctionality()
	{
		$entity = $this->data();
		$serialized = serialize($entity);
		$this->assertInternalType('string', $serialized);
		$unserialized = unserialize($serialized);
		$this->assertEquals($entity, $unserialized);
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class BaseEntity extends Nette\Object
{

	/**
	 * @var string
	 */
	public $foo;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;



	/**
	 * @param int $id
	 * @param string $name
	 */
	public function __construct($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
	}



	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		if (isset($this->{$name})) {
			return $this->{$name};
		}
		return parent::__get($name);
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ConcreteEntity extends BaseEntity
{

	/**
	 * @var string
	 */
	public $bar;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	protected $baz;



	/**
	 * @param int $id
	 * @param string $name
	 * @param string $concreteName
	 * @param string $baz
	 */
	public function __construct($id, $name, $concreteName, $baz)
	{
		parent::__construct($id, $name);
		$this->name = $concreteName;
		$this->baz = $baz;
	}



	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function &__get($name)
	{
		if (isset($this->{$name})) {
			return $this->{$name};
		}
		return parent::__get($name);
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SexyEntity extends ConcreteEntity implements \Serializable
{

	/**
	 * @var string
	 */
	public $lorem;



	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function &__get($name)
	{
		if (isset($this->{$name})) {
			return $this->{$name};
		}
		return parent::__get($name);
	}



	/**
	 * @return string
	 */
	public function serialize()
	{
		return SerializableMixin::serialize($this);
	}



	/**
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		SerializableMixin::unserialize($this, $serialized);
	}

}
