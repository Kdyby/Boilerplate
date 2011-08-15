<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\EventDispatcher;

use Kdyby;
use Kdyby\EventDispatcher\EventArgs;
use Nette;



/**
 * @author Filip Procházka
 */
class EventArgsTest extends Kdyby\Testing\Test
{

	public function testImplementsDoctrineEventArgs()
	{
		$args = new EventArgsMock();
		$this->assertInstanceOf('Doctrine\Common\EventArgs', $args);
	}

}