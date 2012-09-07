<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
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
