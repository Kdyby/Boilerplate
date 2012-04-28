<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Mapping;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Kdyby;
use Kdyby\Doctrine\Mapping\EntityMetadataMapper;
use Nette;
use Nette\Reflection\ClassType;
use Nette\Reflection\Property;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ClassMetadata extends Doctrine\ORM\Mapping\ClassMetadata
{

	/** @var string */
	public $customRepositoryClassName = 'Kdyby\Doctrine\Dao';

}
