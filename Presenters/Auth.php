<?php

namespace Kdyby\Presenter;

use Nette;
use Kdyby;



/**
 * Description of Auth
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Auth extends Base
{

	public function actionLogout()
	{
		$this->getUser()->logout();
		$this->redirect(':Front:Homepage:');
	}

}
