<?php

namespace Kdyby\Components\Grinder\Toolbar;

use Kdyby;
use Nette;
use Nette\Forms\SubmitButton;



/**
 * @author Filip Procházka
 */
class ButtonAction extends BaseAction
{

	/**
	 * @param string $caption
	 */
	public function __construct($caption)
	{
		parent::__construct(new SubmitButton($caption));
	}

}