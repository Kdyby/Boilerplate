<?php

namespace Kdyby\Components\Grinder\Forms;

use Kdyby;
use Nette;
use Nette\Application\UI\Form;



/**
 * @author Filip Procházka
 */
class GridForm extends Form
{

	public function __construct()
	{
		parent::__construct(NULL, NULL);
		$this->addContainer('toolbar');
	}

}