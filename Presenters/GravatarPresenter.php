<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Presenter;

use Nette\Image;
use Kdyby\Application\Response\GravatarResponse;



/**
 * @author Mikulas Dite
 */
class GravatarPresenter extends BasePresenter
{

	public function actionDefault($email, $size = 80)
	{
		$this->sendResponse(new GravatarResponse($email, $size));
	}
}
