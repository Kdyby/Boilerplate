<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder;

use Doctrine\ORM\EntityManager;
use Kdyby;
use Nette;
use Nette\Http;



/**
 * @author Filip Procházka
 */
class GridFactory extends Nette\Object
{

	/** @var EntityManager */
	private $entityManager;

	/** @var Http\Session */
	private $session;



	/**
	 * @param EntityManager $entityManager
	 * @param Http\Session|NULL $session
	 */
	public function __construct(EntityManager $entityManager, Http\Session $session = NULL)
	{
		$this->entityManager = $entityManager;
		$this->session = $session;
	}



	/**
	 * @param string $entity
	 * @return Grinder\Grid
	 */
	public function createNew($entity)
	{
		$grid = new Grid(new Models\SimpleDoctrineModel($this->entityManager, $entity));

		if ($this->session !== NULL) {
			$grid->setUpProtection($this->session);
		}

		return $grid;
	}

}