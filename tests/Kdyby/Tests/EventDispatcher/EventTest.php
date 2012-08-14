<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\EventDispatcher;

use Kdyby;
use Kdyby\EventDispatcher\Event;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EventTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return FooMock
	 */
	public function dataDispatch()
	{
		$foo = new FooMock();
		$foo->onBar = new Event('bar');
		$foo->onBar[] = function ($lorem) {
			echo $lorem;
		};
		$foo->onBar[] = function ($lorem) {
			echo $lorem + 1;
		};

		return $foo;
	}



	public function testDispatch_Method()
	{
		ob_start();
		$foo = $this->dataDispatch();
		$foo->onBar->dispatch(array(10));
		$this->assertSame('1011', ob_get_clean());
	}



	public function testDispatch_Invoke()
	{
		ob_start();
		try {
			$foo = $this->dataDispatch();
			$foo->onBar(15);
			$this->assertSame('1516', ob_get_clean());

		} catch (Nette\MemberAccessException $e) {
			ob_end_clean();
			$this->markTestSkipped("Nette Framework issue: https://github.com/nette/nette/issues/730");
		}
	}



	/**
	 */
	public function testDispatch_toManager()
	{
		// create
		$evm = new Kdyby\EventDispatcher\EventManager();
		$foo = new FooMock();
		$foo->onMagic = new Event('onMagic', $evm);

		// register
		$evm->addSubscriber(new LoremListener());
		$foo->onMagic[] = function (FooMock $foo, $int) {
			echo $int * 3;
		};

		ob_start();
		$foo->onMagic($foo, 2);
		$this->assertSame('64', ob_get_clean());


		ob_start();
		$foo->onMagic->dispatch(array($foo, 2));
		$this->assertSame('64', ob_get_clean());
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method onBar($lorem)
 * @method onMagic(FooMock $foo, $int)
 */
class FooMock extends Nette\Object
{

	/**
	 * @var array|callable[]|Event
	 */
	public $onBar = array();

	/**
	 * @var array|callable[]|Event
	 */
	public $onMagic = array();

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LoremListener extends Nette\Object implements Kdyby\EventDispatcher\EventSubscriber
{

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			'onMagic'
		);
	}



	/**
	 * @param FooMock $foo
	 * @param $int
	 */
	public function onMagic(FooMock $foo, $int)
	{
		echo $int * 2;
	}

}
