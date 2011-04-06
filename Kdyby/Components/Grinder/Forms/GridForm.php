<?php

namespace Kdyby\Components\Grinder\Forms;

use Kdyby;
use Nette;
use Nette\Application\AppForm;



/**
 * @author Filip Procházka
 */
class GridForm extends AppForm
{

	public function __construct()
	{
		parent::__construct(NULL, NULL);
		$this->addContainer('toolbar');
	}

}