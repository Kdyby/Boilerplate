<?php

namespace Kdyby\Form\Mapper;

use Nette;
use Kdyby;



/**
 * Description of Base
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Base extends Nette\Object
{
	private $form;



	public function __construct(Kdyby\Form\Base $form)
	{
		$this->form = $form;
	}


	public function getForm()
	{
		return $this->form;
	}

}
