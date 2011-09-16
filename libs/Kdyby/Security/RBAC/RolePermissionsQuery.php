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
class RolePermissionsQuery extends Kdyby\Doctrine\ORM\QueryObjectBase
{

	/** @var Role */
	private $role;



	/**
	 * @param Role $role
	 * @param Paginator $paginator
	 */
	public function __construct(Role $role, Paginator $paginator = NULL)
	{
		parent::__construct($paginator);
		$this->role = $role;
	}



	/**
	 * @param EntityRepository $repository
	 * @return Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(EntityRepository $repository)
	{
		return $repository->createQueryBuilder('perm')->select('perm', 'priv', 'act', 'res')
			->innerJoin('perm.privilege', 'priv')
			->innerJoin('perm.role', 'role')
			->innerJoin('priv.action', 'act')
			->innerJoin('priv.resource', 'res')
			->andWhereEquals('role', $repository->getIdentifierValues($this->role));
	}

}