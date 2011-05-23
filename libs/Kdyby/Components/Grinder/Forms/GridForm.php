<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

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