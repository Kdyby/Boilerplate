<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application\UI;

use Nette\Image;
use Kdyby\Application\Response\Gravatar;



/**
 * @author Mikulas Dite
 */
class GravatarPresenter extends Presenter
{

	public function actionDefault($email, $size = 80)
	{
		$this->sendResponse(new Gravatar($email, $size));
	}

}
