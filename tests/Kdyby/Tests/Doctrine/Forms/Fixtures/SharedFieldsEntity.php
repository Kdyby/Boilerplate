<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Doctrine\Forms\Fixtures;

use Kdyby;
use Nette;



/**
 * @Orm:MappedSuperclass()
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SharedFieldsEntity extends Nette\Object
{

	/**
	 * @Orm:Id
	 * @Orm:Column(type="integer")
	 * @Orm:GeneratedValue
	 * @var integer
	 */
	public $id;

}
