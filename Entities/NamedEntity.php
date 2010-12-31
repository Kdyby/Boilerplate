<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Doctrine;

use Nette;
use Nette\Environment;
use Nette\Caching\Cache AS NCache;



/**
 * @MappedSuperclass
 *
 * @property int $id
 * @property string $name
 */
abstract class NamedEntity extends BaseEntity
{

	/** @Id @Column(type="integer") */
	private $id;

	/** @Column(type="string") */
	private $name;



	public function getId() { return $this->id; }
	public function setId($id) { $this->id = $id; }

	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; }

}