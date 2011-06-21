<?php

namespace Kdyby\Doctrine;

use Doctrine;
use Kdyby;
use Kdyby\Components\Grinder;
use Nette;



/**
 * @author Filip Procházka
 */
class QueryBuilder extends Doctrine\ORM\QueryBuilder
{

	/**
	 * Gets the root entity of the query. This is the first entity alias involved
	 * in the construction of the query.
	 *
	 * <code>
	 *     $qb = $em->createQueryBuilder()
	 *         ->select('u')
	 *         ->from('User', 'u');
	 *
	 *     echo $qb->getRootEntity(); // User
	 * </code>
	 *
	 * @return string $rootEntity
	 */
	public function getRootEntity()
	{
		$from = $this->getDQLPart('from');
		return $from[0]->getFrom();
	}



	/**
	 * Get Gridito model
	 * @return Grinder\Models\DoctrineQueryBuilderModel
	 */
	public function getGrinderModel()
	{
		$em = $this->getEntityManager();
		$identifier = $em->getClassMetadata($this->getRootEntity())->getSingleIdentifierFieldName();

		$grinderModel = new Grinder\DoctrineQueryBuilderModel($this);
		$grinderModel->setPrimaryKey($identifier);

		return $grinderModel;
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassType(/*5.2*$this*//**/get_called_class()/**/);
	}



	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
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