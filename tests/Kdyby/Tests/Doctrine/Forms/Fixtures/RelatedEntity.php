<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Doctrine\Forms\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @ORM\Entity()
 * @author Filip Procházka <filip@prochazka.su>
 */
class RelatedEntity extends SharedFieldsEntity
{

	/**
	 * @ORM\Column(type="string")
	 */
	public $name;

	/**
	 * @ORM\ManyToOne(targetEntity="RootEntity", inversedBy="children", cascade={"persist"})
	 * @var \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity
	 */
	public $daddy;

	/**
	 * @ORM\ManyToMany(targetEntity="RootEntity", mappedBy="buddies", cascade={"persist"})
	 * @var \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity[]
	 */
	public $buddies;



	/**
	 * @param string $name
	 * @param \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity $daddy
	 */
	public function __construct($name = NULL, RootEntity $daddy = NULL)
	{
		$this->name = $name;
		$this->daddy = $daddy;
		$this->buddies = new ArrayCollection();
	}

}
