<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Entities;

use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Mapping as ORM;
use Nette;
use Kdyby;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @ORM\MappedSuperclass()
 *
 * @property-read int $id
 */
abstract class IdentifiedEntity extends BaseEntity
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer
	 */
	private $id;



	/**
	 * @return integer
	 */
	final public function getId()
	{
		if ($this instanceof Proxy && !$this->__isInitialized__ && !$this->id) {
			$identifier = $this->getReflection()->getProperty('_identifier');
			$identifier->setAccessible(TRUE);
			$id = $identifier->getValue($this);
			$this->id = reset($id);
		}

		return $this->id;
	}

}
