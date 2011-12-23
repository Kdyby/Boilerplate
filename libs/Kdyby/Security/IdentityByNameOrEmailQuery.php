<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security;

use Doctrine;
use Kdyby\Persistence\IQueryable;
use Kdyby;
use Nette;
use Nette\Utils\Paginator;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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
		return $this->createQueryBuilder('u')
			->where('u.username = :nameOrEmail')
			->orWhere('u.email = :nameOrEmail')
			->setParameter('nameOrEmail', $this->nameOrEmail);
	}

}
