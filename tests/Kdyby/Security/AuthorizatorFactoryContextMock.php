<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Kdyby;
use Kdyby\Testing\TestCase;
use Nette;
use Nette\Http;



/**
 * @author Filip Procházka
 */
class AuthorizatorFactoryContextMock extends Kdyby\Security\AuthorizatorFactoryContext
{

	/** @var TestCase */
	private $test;



	/**
	 * @param TestCase $test
	 * @param ObjectManager $workspace
	 */
	public function __construct(TestCase $test, ObjectManager $workspace)
	{
		parent::__construct($test->getMock(
				'Nette\Http\User', array(), array(), '', FALSE
			), $test->getMock(
				'Nette\Http\Session', array(), array(), '', FALSE
			), $workspace);
		$this->test = $test;
	}

}