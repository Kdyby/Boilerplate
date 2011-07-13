<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Components\Grinder\Filters;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class SourceMock extends Nette\Object
{

	public function source()
	{
		return 3.145;
	}


}