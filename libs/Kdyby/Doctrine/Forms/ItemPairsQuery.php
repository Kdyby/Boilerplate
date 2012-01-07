<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Forms;

use Kdyby;
use Kdyby\Doctrine\Mapping\ClassMetadata;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ItemPairsQuery extends Kdyby\Doctrine\QueryObjectBase
{

	/** @var object */
	private $entity;

	/** @var string */
	private $field;

	/** @var string */
	private $value;

	/** @var string */
	private $key;



	/**
	 * @param string $entity
	 * @param string $field
	 * @param string $value
	 * @param string $key
	 */
	public function __construct($entity, $field, $value, $key = 'id')
	{
		$this->entity = $entity;
		$this->field = $field;
		$this->value = $value;
		$this->key = $key;
	}



	/**
	 * @param \Kdyby\Persistence\IQueryable $repository
	 *
	 * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(Kdyby\Persistence\IQueryable $repository)
	{
		return $repository->createQuery(
			"SELECT i.$this->key, i.$this->value FROM " . get_class($this->entity) . ' e '.
			"LEFT JOIN e.$this->field i ".
			"WHERE e = :id"
		)->setParameter('id', $this->entity);
	}

}
