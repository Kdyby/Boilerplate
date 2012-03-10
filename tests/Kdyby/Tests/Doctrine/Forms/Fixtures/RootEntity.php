<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Doctrine\Forms\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @ORM\Entity()
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RootEntity extends SharedFieldsEntity
{

	/**
	 * @ORM\Column(type="string")
	 */
	public $name;

	/**
	 * @ORM\ManyToOne(targetEntity="RelatedEntity", cascade={"persist"})
	 * @var \Kdyby\Tests\Doctrine\Forms\Fixtures\RelatedEntity
	 */
	public $daddy;

	/**
	 * @ORM\OneToMany(targetEntity="RelatedEntity", mappedBy="daddy", cascade={"persist"})
	 * @var \Kdyby\Tests\Doctrine\Forms\Fixtures\RelatedEntity[]
	 */
	public $children;

	/**
	 * @ORM\ManyToMany(targetEntity="RelatedEntity", inversedBy="buddies", cascade={"persist"})
	 * @var \Kdyby\Tests\Doctrine\Forms\Fixtures\RelatedEntity[]
	 */
	public $buddies;



	/**
	 * @param string $name
	 */
	public function __construct($name = NULL)
	{
		$this->name = $name;
		$this->children = new ArrayCollection();
		$this->buddies = new ArrayCollection();
	}

}
