<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components;

use Kdyby;
use Kdyby\Application\UI;
use Kdyby\Application\RequestManager;
use Kdyby\Security\User;
use Nette;
use Nette\Security as NS;



/**
 * @author Filip Procházka
 */
class SignInForm extends UI\Form
{

	/** @var User */
	private $user;

	/** @var RequestManager */
	private $requestManager;



	/**
	 * @param User $user
	 * @param RequestManager $requestManager
	 */
	public function __construct(User $user, RequestManager $requestManager = NULL)
	{
		parent::__construct();

		$this->user = $user;
		$this->requestManager = $requestManager;

		$this->addText('username', 'Username')
			->setRequired('Please provide a username.');

		$this->addPassword('password', 'Password')
			->setRequired('Please provide a password.');

		$this->addCheckbox('remember', 'Remember me');

		$this->addSubmit('sign', 'Sign in')
			->onClick[] = callback($this, 'SignInClicked');
	}



	public function SignInClicked()
	{
		try {
			$values = $this->getValues();

			if ($values->remember) {
				$this->user->setExpiration('+ 14 days', FALSE);

			} else {
				$this->user->setExpiration('+ 20 minutes', TRUE);
			}

			$this->user->login($values->username, $values->password);

			if ($this->requestManager) {
				$this->requestManager->restoreRequest($this->presenter->backlink);
			}

			$this->presenter->redirect(':Backend:Dashboard:');

		} catch (NS\AuthenticationException $e) {
			$this->addError($e->getMessage());
		}
	}

}