<?php

namespace Kdyby\Components\Navigation\Entities;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;



/**
 * @Entity
 */
class NavigationRegistryEntity extends NavigationRegistry
{

	/** @Column(type="string") @var string */
	private $entityClass;

	/** @Column(type="integer") @var int */
	private $entityRootId;

	/** @Column(type="integer") @var int */
	private $targetEntityTreeLevel;



	/**
	 * @return string
	 */
	public function getEntityClass()
	{
		return $this->entityClass;
	}



	/**
	 * @param string $entityClass
	 */
	public function setEntityClass($entityClass)
	{
		$this->entityClass = $entityClass;
	}



	/**
	 * @return int
	 */
	public function getEntityRootId()
	{
		return $this->entityRootId;
	}



	/**
	 * @param int $entityRootId
	 */
	public function setEntityRootId($entityRootId)
	{
		$this->entityRootId = $entityRootId;
	}



	/**
	 * @return int
	 */
	public function getTargetEntityTreeLevel()
	{
		return $this->targetEntityTreeLevel;
	}



	/**
	 * @param int $targetEntityTreeLevel
	 */
	public function setTargetEntityTreeLevel($targetEntityTreeLevel)
	{
		$this->targetEntityTreeLevel = $targetEntityTreeLevel;
	}

}