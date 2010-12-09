<?php

namespace Kdyby\Presenter;

use Nette;
use Kdyby;



/**
 * Description of Auth
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class AuthPresenter extends BasePresenter
{

	public function actionLogout()
	{
		$this->getUser()->logout();
		$this->redirect(':Front:Homepage:');
	}

}
