<?php

namespace Kdyby\Components\Grinder\Actions;

use Kdyby;
use Nette;
use Nette\Forms\SubmitButton;



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