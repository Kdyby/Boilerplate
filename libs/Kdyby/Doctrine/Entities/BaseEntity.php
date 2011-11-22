<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Entities;

use Nette;
use Nette\Environment;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @author Jan Smitka
 *
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 *
 * @property-read int $id
 */
abstract class BaseEntity extends Nette\Object
{

	public function __construct() { }

}