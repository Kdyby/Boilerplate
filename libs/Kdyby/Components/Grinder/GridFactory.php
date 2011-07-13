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
use Doctrine\Common\Persistence\ObjectManager;
use Kdyby;
use Nette;
use Nette\Http;



/**
 * @author Filip Procházka
 */
class GridFactory extends Nette\Object
{

	/** @var ObjectManager */
	private $manager;

	/** @var Http\Session */
	private $session;



	/**
	 * @param ObjectManager $workspace
	 * @param Http\Session|NULL $session
	 */
	public function __construct(ObjectManager $workspace, Http\Session $session = NULL)
	{
		$this->workspace = $workspace;
		$this->session = $session;
	}



	/**
	 * @param string $className
	 * @return Grinder\Grid
	 */
	public function createNew($className)
	{
		$manager = $this->workspace;
		if ($this->workspace instanceof Kdyby\Doctrine\Workspace) {
			$manager = $this->workspace->getManager($className);
		}

		$grid = new Grid(new Models\SimpleDoctrineModel($manager, $entity));

		if ($this->session !== NULL) {
			$grid->setUpProtection($this->session);
		}

		return $grid;
	}

}