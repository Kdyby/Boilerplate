<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Security\RBAC;

use Doctrine;
use Kdyby;
use Kdyby\Persistence\IQueryable;
use Nette;
use Nette\Utils\Paginator;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DivisionResourcesQuery extends Kdyby\Doctrine\QueryObjectBase
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
	 * @param IQueryable $repository
	 * @return Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(IQueryable $repository)
	{
		return $repository->createQuery(
				"SELECT r FROM Kdyby\Security\RBAC\Resource r, " .
					"Kdyby\Security\RBAC\Division d " .
				"INNER JOIN d.privileges p ".
				"WHERE p.resource = r AND d = :division " .
				"ORDER BY r.name ASC, r.description ASC"
			)->setParameter('division', $this->division);
	}

}
