<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Security;

use Doctrine;
use Kdyby\Persistence\IQueryable;
use Kdyby;
use Nette;
use Nette\Utils\Paginator;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class IdentityByNameOrEmailQuery extends Kdyby\Doctrine\QueryObjectBase
{

	/** @var string */
	private $nameOrEmail;



	/**
	 * @param string $nameOrEmail
	 * @param \Nette\Utils\Paginator $paginator
	 */
	public function __construct($nameOrEmail, Paginator $paginator = NULL)
	{
		parent::__construct($paginator);
		$this->nameOrEmail = $nameOrEmail;
	}



	/**
	 * @return string
	 */
	public function getNameOrEmail()
	{
		return $this->nameOrEmail;
	}



	/**
	 * @param \Kdyby\Persistence\IQueryable $repository
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(IQueryable $repository)
	{
		return $repository->createQueryBuilder('u')
			->leftJoin('u.info', 'i')
			->where('u.username = :nameOrEmail')
			->orWhere('u.email = :nameOrEmail')
			->setParameter('nameOrEmail', $this->nameOrEmail);
	}

}
