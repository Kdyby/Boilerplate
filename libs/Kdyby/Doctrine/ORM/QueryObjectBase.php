<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM;

use Doctrine;
use DoctrineExtensions\Paginate\Paginate;
use Kdyby;
use Nette;
use Nette\ObjectMixin;
use Nette\Utils\Paginator;



/**
 * @author Filip Procházka
 */
abstract class QueryObjectBase implements IQueryObject
{

	/** @var Paginator */
	private $paginator;



	/**
	 * @param Paginator $paginator
	 */
	public function __construct(Paginator $paginator = NULL)
	{
		$this->paginator = $paginator;
	}



	/**
	 * @return Paginator
	 */
	public function getPaginator()
	{
		return $this->paginator;
	}



	/**
	 * @param EntityRepository $repository
	 * @return Doctrine\ORM\Query|Doctrine\ORM\QueryBuilder
	 */
	protected abstract function doCreateQuery(EntityRepository $repository);



	/**
	 * @param EntityRepository $repository
	 * @return Doctrine\ORM\Query
	 */
	protected function getQuery(EntityRepository $repository)
	{
		$query = $this->doCreateQuery($repository);
		if ($query instanceof Doctrine\ORM\QueryBuilder) {
			return $query->getQuery();

		} elseif ($query instanceof Doctrine\ORM\Query) {
			return $query;
		}

		$class = $this->getReflection()->getMethod('doCreateQuery')->getDeclaringClass();
		throw new Nette\InvalidStateException("Method " . $class . "::doCreateQuery() must return" .
				" instanceof Doctrine\\ORM\\Query or instaceof Doctrine\\ORM\\QueryBuilder, " .
				Kdyby\Tools\Mixed::getType($query) . " given.");
	}



	/**
	 * @param EntityRepository $repository
	 * @return integer
	 */
	public function count(EntityRepository $repository)
	{
		return Paginate::getTotalQueryResults($this->getQuery($repository));
	}



	/**
	 * @param EntityRepository $repository
	 * @return array
	 */
	public function fetch(EntityRepository $repository)
	{
		$query = $this->getQuery($repository);

		if ($this->paginator) {
			$query = Paginate::getPaginateQuery($query, $this->paginator->getOffset(), $this->paginator->getLength()); // Step 2 and 3

		} else {
			$query = $query->setMaxResults(NULL)->setFirstResult(NULL);
		}

		return $query->getResult();
	}



	/**
	 * @param EntityRepository $repository
	 * @return object
	 */
	public function fetchOne(EntityRepository $repository)
	{
		return $this->getQuery($repository)
			->setFirstResult(NULL)
			->setMaxResults(1)
			->getSingleResult();
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassType(/*5.2*$this*//**/get_called_class()/**/);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}