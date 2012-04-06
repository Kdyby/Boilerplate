<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Doctrine\Entities;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class BaseEntityTest extends Kdyby\Tests\TestCase
{

	/**
	 * @expectedException Nette\MemberAccessException
	 * @expectedExceptionMessage Cannot unset the property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$one.
	 */
	public function testUnsetPrivateException()
	{
		$entity = new ConcreteEntity();
		unset($entity->one);
	}



	/**
	 * @expectedException Nette\MemberAccessException
	 * @expectedExceptionMessage Cannot unset the property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$two.
	 */
	public function testUnsetProtectedException()
	{
		$entity = new ConcreteEntity();
		unset($entity->two);
	}



	public function testIsset()
	{
		$entity = new ConcreteEntity();
		$this->assertFalse(isset($entity->one));
		$this->assertTrue(isset($entity->two));
		$this->assertTrue(isset($entity->three));
		$this->assertFalse(isset($entity->ones));
		$this->assertTrue(isset($entity->twos));
		$this->assertTrue(isset($entity->proxies));
		$this->assertTrue(isset($entity->threes));
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Cannot read an undeclared property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$one.
	 */
	public function testGetPrivateException()
	{
		$entity = new ConcreteEntity();
		$entity->one;
	}



	public function testGetProtected()
	{
		$entity = new ConcreteEntity();
		$this->assertEquals(2, $entity->two->id);
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Cannot read an undeclared property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$ones.
	 */
	public function testGetPrivateCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->ones;
	}



	public function testGetProtectedCollection()
	{
		$entity = new ConcreteEntity();
		$this->assertEquals($entity->twos, $entity->getTwos());
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Cannot write to an undeclared property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$one.
	 */
	public function testSetPrivateException()
	{
		$entity = new ConcreteEntity();
		$entity->one = 1;
	}



	public function testSetProtected()
	{
		$entity = new ConcreteEntity();
		$entity->two = 2;
		$this->assertEquals(2, $entity->two);
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Cannot write to an undeclared property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$ones.
	 */
	public function testSetPrivateCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->ones = 1;
	}



	/**
	 * @expectedException Kdyby\UnexpectedValueException
	 * @expectedExceptionMessage Class property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$twos is an instance of Doctrine\Common\Collections\Collection. Use add<property>() and remove<property>() methods to manipulate it or declare your own.
	 */
	public function testSetProtectedCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->twos = 1;
	}



	/**
	 * @expectedException Kdyby\UnexpectedValueException
	 * @expectedExceptionMessage Class property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$proxies is an instance of Doctrine\Common\Collections\Collection. Use add<property>() and remove<property>() methods to manipulate it or declare your own.
	 */
	public function testSetProtectedCollection2Exception()
	{
		$entity = new ConcreteEntity();
		$entity->proxies = 1;
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Kdyby\Tests\Doctrine\Entities\ConcreteEntity::setOne().
	 */
	public function testCallSetterOnPrivateException()
	{
		$entity = new ConcreteEntity();
		$entity->setOne(1);
	}



	public function testCallSetterOnProtected()
	{
		$entity = new ConcreteEntity();
		$entity->setTwo(2);
		$this->assertEquals(2, $entity->two);
	}



	public function testValidSetterProvidesFluentInterface()
	{
		$entity = new ConcreteEntity();
		$this->assertSame($entity, $entity->setTwo(2));
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Kdyby\Tests\Doctrine\Entities\ConcreteEntity::setOnes().
	 */
	public function testCallSetterOnPrivateCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->setOnes(1);
	}



	/**
	 * @expectedException Kdyby\UnexpectedValueException
	 * @expectedExceptionMessage Class property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$twos is an instance of Doctrine\Common\Collections\Collection. Use add<property>() and remove<property>() methods to manipulate it or declare your own.
	 */
	public function testCallSetterOnProtectedCollection()
	{
		$entity = new ConcreteEntity();
		$entity->setTwos(2);
	}



	/**
	 * @expectedException Kdyby\UnexpectedValueException
	 * @expectedExceptionMessage Class property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$proxies is an instance of Doctrine\Common\Collections\Collection. Use add<property>() and remove<property>() methods to manipulate it or declare your own.
	 */
	public function testCallSetterOnProtected2Collection()
	{
		$entity = new ConcreteEntity();
		$entity->setProxies(3);
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Kdyby\Tests\Doctrine\Entities\ConcreteEntity::getOne().
	 */
	public function testCallGetterOnPrivateException()
	{
		$entity = new ConcreteEntity();
		$entity->getOne();
	}



	public function testCallGetterOnProtected()
	{
		$entity = new ConcreteEntity();
		$this->assertEquals(2, $entity->getTwo()->id);
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Kdyby\Tests\Doctrine\Entities\ConcreteEntity::getOnes().
	 */
	public function testCallGetterOnPrivateCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->getOnes();
	}



	public function testCallGetterOnProtectedCollection()
	{
		$entity = new ConcreteEntity();
		$this->assertEquals(array((object)array('id' => 2)), $entity->getTwos());
		$this->assertEquals(array((object)array('id' => 3)), $entity->getProxies());
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Kdyby\Tests\Doctrine\Entities\ConcreteEntity::thousand().
	 */
	public function testCallNonExistingMethodException()
	{
		$entity = new ConcreteEntity();
		$entity->thousand(1000);
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Kdyby\Tests\Doctrine\Entities\ConcreteEntity::addOne().
	 */
	public function testCallAddOnPrivateCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->addOne((object)array('id' => 1));
	}



	/**
	 * @expectedException Kdyby\UnexpectedValueException
	 * @expectedExceptionMessage Class property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$four is not an instance of Doctrine\Common\Collections\Collection.
	 */
	public function testCallAddOnNonCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->addFour((object)array('id' => 4));
	}



	public function testCallAddOnProtectedCollection()
	{
		$entity = new ConcreteEntity();
		$entity->addTwo($a = (object)array('id' => 2));
		$this->assertContains($a, $entity->getTwos());

		$entity->addProxy($b = (object)array('id' => 3));
		$this->assertContains($b, $entity->getProxies());
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Kdyby\Tests\Doctrine\Entities\ConcreteEntity::hasOne().
	 */
	public function testCallHasOnPrivateCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->hasOne((object)array('id' => 1));
	}



	/**
	 * @expectedException Kdyby\UnexpectedValueException
	 * @expectedExceptionMessage Class property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$four is not an instance of Doctrine\Common\Collections\Collection.
	 */
	public function testCallHasOnNonCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->hasFour((object)array('id' => 4));
	}



	public function testCallHasOnProtectedCollection()
	{
		$entity = new ConcreteEntity();
		$this->assertFalse($entity->hasTwo((object)array('id' => 2)));
		$this->assertFalse($entity->hasProxy((object)array('id' => 3)));

		$this->assertNotEmpty($twos = $entity->getTwos());
		$this->assertTrue($entity->hasTwo(reset($twos)));

		$this->assertNotEmpty($proxies = $entity->getProxies());
		$this->assertTrue($entity->hasProxy(reset($proxies)));
	}



	/**
	 * @expectedException Kdyby\MemberAccessException
	 * @expectedExceptionMessage Call to undefined method Kdyby\Tests\Doctrine\Entities\ConcreteEntity::removeOne().
	 */
	public function testCallRemoveOnPrivateCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->removeOne((object)array('id' => 1));
	}



	/**
	 * @expectedException Kdyby\UnexpectedValueException
	 * @expectedExceptionMessage Class property Kdyby\Tests\Doctrine\Entities\ConcreteEntity::$four is not an instance of Doctrine\Common\Collections\Collection.
	 */
	public function testCallRemoveOnNonCollectionException()
	{
		$entity = new ConcreteEntity();
		$entity->removeFour((object)array('id' => 4));
	}



	public function testCallRemoveOnProtectedCollection()
	{
		$entity = new ConcreteEntity();
		$this->assertNotEmpty($twos = $entity->getTwos());
		$entity->removeTwo(reset($twos));
		$this->assertEmpty($entity->getTwos());

		$this->assertNotEmpty($proxies = $entity->getProxies());
		$entity->removeProxy(reset($proxies));
		$this->assertEmpty($entity->getProxies());
	}



	public function testGetterHaveHigherPriority()
	{
		$entity = new ConcreteEntity();
		$this->assertEquals(4, $entity->something);
	}



	public function testSetterHaveHigherPriority()
	{
		$entity = new ConcreteEntity();
		$entity->something = 4;
		$this->assertAttributeEquals(2, 'something', $entity);
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ConcreteEntity extends BaseEntity
{

	/**
	 * @var array events
	 */
	private $onSomething = array();

	/**
	 * @var object
	 */
	private $one;

	/**
	 * @var object
	 */
	protected $two;

	/**
	 * @var object
	 */
	protected $four;

	/**
	 * @var object
	 */
	public $three;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	private $ones;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $twos;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $proxies;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	public $threes;

	/**
	 * @var int
	 */
	protected $something = 2;



	/**
	 */
	public function __construct()
	{
		$this->one = (object)array('id' => 1);
		$this->two = (object)array('id' => 2);
		$this->three = (object)array('id' => 3);

		$this->ones = new ArrayCollection(array((object)array('id' => 1)));
		$this->twos = new ArrayCollection(array((object)array('id' => 2)));
		$this->proxies = new ArrayCollection(array((object)array('id' => 3)));
		$this->threes = new ArrayCollection(array((object)array('id' => 4)));
	}



	/**
	 * @param int $something
	 */
	public function setSomething($something)
	{
		$this->something = (int)ceil($something / 2);
	}



	/**
	 * @return int
	 */
	public function getSomething()
	{
		return $this->something * 2;
	}

}
