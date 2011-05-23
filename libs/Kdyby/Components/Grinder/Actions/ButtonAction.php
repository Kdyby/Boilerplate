<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Actions;

use Kdyby;
use Nette;
use Nette\Forms\Controls\SubmitButton;



/**
 * @author Filip Procházka
 */
class ButtonAction extends FormAction
{

	/**
	 * @param string $caption
	 */
	public function __construct($caption = NULL)
	{
		parent::__construct(new SubmitButton($caption));
	}

}