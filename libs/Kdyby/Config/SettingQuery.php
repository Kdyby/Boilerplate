<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Config;

use Doctrine;
use Kdyby;
use Kdyby\Doctrine\ORM\EntityRepository;
use Nette;
use Nette\Utils\Paginator;



/**
 * @author Filip Procházka
 */
class SettingQuery extends Kdyby\Doctrine\ORM\QueryObjectBase
{

	/** @var string */
	private $name;

	/** @var string */
	private $section;



	/**
	 * @param string $name
	 * @param string $section
	 * @param Paginator $paginator
	 */
	public function __construct($name, $section = NULL, Paginator $paginator = NULL)
	{
		parent::__construct($paginator);
		$this->name = $name;
		$this->section = $section;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @return string
	 */
	public function getSection()
	{
		return $this->section;
	}



	/**
	 * @param EntityRepository $repository
	 * @return Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(EntityRepository $repository)
	{
		$qb = $repository->createQueryBuilder('s');

		if ($this->name !== NULL) {
			$qb->andWhere('s.name = :name')
				->setParameter('name', $this->name);
		}

		if ($this->section === NULL) {
			$qb->andWhere('s.section IS NULL');

		} else {
			$qb->andWhere('s.section = :section')
				->setParameter('section', $this->section);
		}

		return $qb;
	}

}