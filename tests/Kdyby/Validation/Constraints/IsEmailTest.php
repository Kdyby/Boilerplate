<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Validation\Constraints;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class IsEmailTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Constraints\IsEmail */
	private $constraint;



	public function setUp()
	{
		$this->constraint = new Kdyby\Validation\Constraints\IsEmail();
	}



	/**
	 * @return array
	 */
	public function getValidEmails()
	{
		return array(
			array("l3tt3rsAndNumb3rs@domain.com"),
			array("has-dash@domain.com"),
			array("hasApostrophe.o'leary@domain.org"),
			array("uncommonTLD@domain.museum"),
			array("uncommonTLD@domain.travel"),
			array("uncommonTLD@domain.mobi"),
			array("countryCodeTLD@domain.uk"),
			array("countryCodeTLD@domain.rw"),
			array("lettersInDomain@911.com"),
			array("underscore_inLocal@domain.net"),
			array("subdomain@sub.domain.com"),
			array("local@dash-inDomain.com"),
			array("dot.inLocal@foo.com"),
			array("a@singleLetterLocal.org"),
			array("singleLetterDomain@x.org"),
			array("singleLetterDomain@x.org"),
			array("&*=?^+{}'~@validCharsInLocal.net"),
			array("foor@bar.newTLD")
		);
	}



	/**
	 * @dataProvider getValidEmails
	 * @param string $email
	 */
	public function testEvaluateValid($email)
	{
		$this->assertTrue($this->constraint->evaluate($email));
	}



	/**
	 * @return array
	 */
	public function getInvalidEmails()
	{
		return array(
			array("missingDomain@.com"),
			array("@missingLocal.org"),
			array("missingatSign.net"),
			array("missingDot@com"),
			array("two@@signs.com"),
			array("colonButNoPort@127.0.0.1:"),
			array(".localStartsWithDot@domain.com"),
			array("localEndsWithDot.@domain.com"),
			array("two..consecutiveDots@domain.com"),
			array("domainStartsWithDash@-domain.com"),
			array("domainEndsWithDash@domain-.com"),
			array("missingTLD@domain."),
			array("! \"#\$%(),/;<>[]`|@invalidCharsInLocal.org"),
			array("invalidCharsInDomain@! \"#\$%(),/;<>_[]`|.org"),
			array("local@SecondLevelDomainNamesAreInvalidIfTheyAreLongerThan64Charactersss.org")
		);
	}



	/**
	 * @dataProvider getInvalidEmails
	 * @param string $email
	 */
	public function testEvaluateInvalid($email)
	{
		$this->assertFalse($this->constraint->evaluate($email));
	}

}