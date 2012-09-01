<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
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
 * @author Filip Procházka <filip@prochazka.su>
 */
class RolePermissionsQuery extends Kdyby\Doctrine\QueryObjectBase
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
	 * @param IQueryable $repository
	 * @return Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(IQueryable $repository)
	{
		return $repository->createQueryBuilder('perm')->select('perm', 'priv', 'act', 'res')
			->innerJoin('perm.privilege', 'priv')
			->innerJoin('perm.role', 'role')
			->innerJoin('priv.action', 'act')
			->innerJoin('priv.resource', 'res')
			->where('role = :role')
				->setParameter('role', $this->role);
	}

}
