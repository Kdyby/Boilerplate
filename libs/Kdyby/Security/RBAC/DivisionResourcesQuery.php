<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security\RBAC;

use Doctrine;
use Kdyby;
use Kdyby\Doctrine\ORM\EntityRepository;
use Nette;
use Nette\Utils\Paginator;



/**
 * @author Filip Procházka
 */
class DivisionResourcesQuery extends Kdyby\Doctrine\ORM\QueryObjectBase
{

	/** @var Division */
	private $division;



	/**
	 * @param Division $division
	 * @param Paginator $paginator
	 */
	public function __construct(Division $division, Paginator $paginator = NULL)
	{
		parent::__construct($paginator);
		$this->division = $division;
	}



	/**
	 * @param EntityRepository $repository
	 * @return Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(EntityRepository $repository)
	{
		return $repository->createQueryBuilder('r')->resetDQLPart('from')
			->from('Kdyby\Security\RBAC\Division', 'd')
			->innerJoin('d.privileges', 'p')
			->innerJoin('p.resource', 'r')
			->andWhereEquals('d', $repository->getIdentifierValues($this->division))
			->orderBy(array('r.name' => 'asc', 'r.description' => 'asc'));
	}

}