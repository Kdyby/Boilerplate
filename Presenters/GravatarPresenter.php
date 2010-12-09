<?php

namespace Kdyby\Presenter;

use Nette\Image;
use Kdyby\Application\Response\GravatarResponse;



/**
 * Gravatar presenter
 *
 * @author Mikulas Dite
 */
class GravatarPresenter extends BasePresenter
{

	public function actionDefault($email, $size = 80)
	{
		$this->sendResponse(new GravatarResponse($email, $size));
	}
}
