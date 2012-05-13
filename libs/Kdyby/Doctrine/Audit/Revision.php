<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit;

use Doctrine\ORM\Mapping as ORM;
use Nette;
use Kdyby;



/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @ORM\Entity(readOnly=TRUE)
 * @ORM\Table(name="audit_revisions", indexes={
 * @ORM\Index(name="entity_idx", columns={"className", "entityId"})
 * })
 */
class Revision extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	const TYPE_INSERT = 1;

	const TYPE_UPDATE = 2;

	const TYPE_DELETE = 3;

	/**
	 * @ORM\Column(type="smallint")
	 * @var integer
	 */
	protected $type = self::TYPE_INSERT;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $className;

	/**
	 * @ORM\Column(type="integer")
	 * @var integer
	 */
	private $entityId;

	/**
	 * @ORM\Column(type="text", nullable=TRUE)
	 * @var string
	 */
	private $message;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \Datetime
	 */
	private $createdAt;

	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	private $author;

	/**
	 * This field must be manually completed by hydrator.
	 * @var object
	 */
	private $entity;



	/**
	 * @param $className
	 * @param integer $id
	 * @param int $type
	 * @param string $author
	 * @param string $message
	 */
	public function __construct($className, $id, $type = self::TYPE_INSERT, $author = NULL, $message = NULL)
	{
		$this->className = $className;
		$this->entityId = $id;
		$this->type = $type;
		$this->createdAt = new \DateTime;
		$this->author = $author;
		$this->message = $message;
	}



	/**
	 * @return string
	 */
	public function getAuthor()
	{
		return $this->author;
	}



	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}



	/**
	 * @return \Datetime
	 */
	public function getCreatedAt()
	{
		return clone $this->createdAt;
	}



	/**
	 * @return int
	 */
	public function getEntityId()
	{
		return $this->entityId;
	}



	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}



	/**
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}



	/**
	 * @internal
	 *
	 * @param object $entity
	 *
	 * @throws \Kdyby\InvalidStateException
	 */
	public function injectEntity($entity)
	{
		if ($this->entity) {
			throw new Kdyby\InvalidStateException("Entity is already injected.");
		}

		$this->entity = $entity;
	}



	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}

}
