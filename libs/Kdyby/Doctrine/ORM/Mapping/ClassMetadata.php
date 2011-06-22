<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM\Mapping;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class ClassMetadata extends Doctrine\ORM\Mapping\ClassMetadata
{

	/**
	 * The name of the custom repository class used for the entity class.
	 * (Optional).
	 *
	 * @var string
	 */
	public $customRepositoryClassName = 'Kdyby\Doctrine\ORM\EntityRepository';

}