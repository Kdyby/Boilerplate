<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Security;

use Kdyby;
use Kdyby\Security\User;
use Kdyby\Tests\TestCase;
use Nette;
use Nette\Http\SessionSection;
use Nette\Security\IIdentity;
use Nette\Security\IAuthorizator;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class UserMockBuilder extends Nette\Object
{

	/** @var TestCase */
	private $test;

	/** @var Nette\ArrayHash */
	private $meta;

	/** @var Nette\ArrayHash */
	private $data;

	/** @var SessionSection */
	private $section;

	/** @var Nette\Http\Session */
	private $session;



	/**
	 * @param TestCase $test
	 */
	public function __construct(TestCase $test)
	{
		$this->test = $test;
	}



	/**
	 * @return Nette\ArrayHash
	 */
	public function getMeta()
	{
		return $this->meta;
	}



	/**
	 * @return Nette\ArrayHash
	 */
	public function getData()
	{
		return $this->data;
	}



	/**
	 * @return Nette\ArrayHash
	 */
	public function getSessionSection()
	{
		return $this->section;
	}



	/**
	 * @return Nette\Http\Session
	 */
	public function getSession()
	{
		return $this->session;
	}



	/**
	 * @param IIdentity $identity
	 * @param IAuthorizator $permission
	 * @param string $userNamespace
	 * @return User
	 */
	public function create(IIdentity $identity, IAuthorizator $permission, $userNamespace = '')
	{
		$this->session = $this->test->getMock('Nette\Http\Session', array(), array(), "", FALSE);

		$context = new Kdyby\DI\Container();
		$context->addService('authenticator', new Kdyby\Security\SimpleAuthenticator($identity));
		$context->addService('authorizator', $permission);
		$context->addService('session', $this->session);
		$user = new User($context);

		$sectionName = 'Nette.Web.User/' . $userNamespace;
		$section = new SessionSection($this->session, $sectionName);

		$this->injectMetaAndData($section);
		$this->injectSection($user, $section);

		$user->login();

		return $user;
	}



	/**
	 * @hack Makes session shut up and act like array storage
	 * @param SessionSection $section
	 */
	private function injectMetaAndData(SessionSection $section)
	{
		$metaRefl = $section->getReflection()->getProperty('meta');
		$metaRefl->setAccessible(TRUE);
		$metaRefl->setValue($section, $this->meta = new \ArrayObject());

		$dataRefl = $section->getReflection()->getProperty('data');
		$dataRefl->setAccessible(TRUE);
		$metaRefl->setValue($section, $this->data = new \ArrayObject());
	}



	/**
	 * @hack Makes user shut up and don't touch the session
	 * @param User $user
	 * @param SessionSection $section
	 */
	private function injectSection(User $user, SessionSection $section)
	{
		$sessionRefl = $user->getReflection()->getParentClass()->getProperty('session');
		$sessionRefl->setAccessible(TRUE);
		$sessionRefl->setValue($user, $this->section = $section);
	}

}