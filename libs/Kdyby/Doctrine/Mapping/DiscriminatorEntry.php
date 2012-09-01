<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Mapping;

use Doctrine\Common\Annotations\Annotation;
use Kdyby;
use Nette;



/**
 * @Annotation
 * @author Filip Procházka <filip@prochazka.su>
 */
class DiscriminatorEntry extends Annotation
{

	/**
	 * @var string
	 */
	public $name;

}
