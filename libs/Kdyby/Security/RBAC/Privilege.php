<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security\RBAC;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @Orm:Entity
 * @Orm:Table(name="rbac_privileges",uniqueConstraints={
 * 	@Orm:UniqueConstraint(name="resource_action_uniq", columns={"resource_id", "action_id"})
 * })
 */
class Privilege extends Nette\Object
{
	const DELIMITER = '#';

	/** @Orm:Id @Orm:Column(type="integer") @Orm:GeneratedValue @var integer */
	private $id;

	/**
	 * @var Resource
	 * @Orm:ManyToOne(targetEntity="Resource", cascade={"persist"}, fetch="EAGER")
	 * @Orm:JoinColumn(name="resource_id", referencedColumnName="id")
	 */
	private $resource;

	/**
	 * @var Action
	 * @Orm:ManyToOne(targetEntity="Action", cascade={"persist"}, fetch="EAGER")
	 * @Orm:JoinColumn(name="action_id", referencedColumnName="id")
	 */
	private $action;



	/**
	 * @param Resource $resource
	 * @param Action $action
	 */
	public function __construct(Resource $resource, Action $action)
	{
		$this->resource = $resource;
		$this->action = $action;
	}



	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->getResource()->getName() . self::DELIMITER . $this->getAction()->getName();
	}



	/**
	 * @return Resource
	 */
	public function getResource()
	{
		return $this->resource;
	}



	/**
	 * @return Action
	 */
	public function getAction()
	{
		return $this->action;
	}

}
