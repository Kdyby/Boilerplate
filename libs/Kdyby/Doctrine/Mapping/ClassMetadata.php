<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
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
 * @author Filip Procházka <filip@prochazka.su>
 */
class ClassMetadata extends Doctrine\ORM\Mapping\ClassMetadata
{

	/**
	 * @var string
	 */
	public $customRepositoryClassName = 'Kdyby\Doctrine\Dao';

	/**
	 * @var bool
	 */
	public $auditChanges = FALSE;

	/**
	 * @var array
	 */
	public $auditRelations = array();



	/**
	 * @return bool
	 */
	public function isAudited()
	{
		return $this->auditChanges;
	}



	/**
	 * @param bool $audited
	 */
	public function setAudited($audited = TRUE)
	{
		$this->auditChanges = $audited;
	}



	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array_merge(
			parent::__sleep(),
			array('auditChanges', 'auditRelations')
		);
	}

}
