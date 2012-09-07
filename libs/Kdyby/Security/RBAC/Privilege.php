<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Security\RBAC;

use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 * @ORM\Entity
 * @ORM\Table(name="rbac_privileges",uniqueConstraints={
 * 	@ORM\UniqueConstraint(name="resource_action_uniq", columns={"resource_id", "action_id"})
 * })
 */
class Privilege extends Nette\Object
{
	const DELIMITER = '#';

	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue @var integer */
	private $id;

	/**
	 * @var Resource
	 * @ORM\ManyToOne(targetEntity="Resource", cascade={"persist"}, fetch="EAGER")
	 * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
	 */
	private $resource;

	/**
	 * @var Action
	 * @ORM\ManyToOne(targetEntity="Action", cascade={"persist"}, fetch="EAGER")
	 * @ORM\JoinColumn(name="action_id", referencedColumnName="id")
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
