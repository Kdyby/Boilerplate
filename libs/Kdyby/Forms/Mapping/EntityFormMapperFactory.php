<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Mapping;

use Kdyby;
use Kdyby\Doctrine\Workspace;
use Kdyby\Doctrine\Mapping\TypeMapper;
use Nette;



/**
 * @author Filip Procházka
 */
class EntityFormMapperFactory extends Nette\Object
{

	/** @var Workspace */
	private $workspace;

	/** @var TypeMapper */
	private $typeMapper;



	/**
	 * @param Workspace $workspace
	 * @param TypeMapper $typeMapper
	 */
	public function __construct(Workspace $workspace, TypeMapper $typeMapper)
	{
		$this->workspace = $workspace;
		$this->typeMapper = $typeMapper;
	}



	/**
	 * @return EntityFormMapper
	 */
	public function create()
	{
		return new EntityFormMapper($this->workspace, $this->typeMapper);
	}

}