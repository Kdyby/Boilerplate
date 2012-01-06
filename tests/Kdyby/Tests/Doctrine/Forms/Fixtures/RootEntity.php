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
use Kdyby;
use Nette;



/**
 * @Orm:Entity()
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RootEntity extends SharedFieldsEntity
{

	/**
	 * @Orm:Column(type="string")
	 */
	public $name;

	/**
	 * @Orm:ManyToOne(targetEntity="RelatedEntity", cascade={"persist"})
	 * @var \Kdyby\Tests\Doctrine\Forms\Fixtures\RelatedEntity
	 */
	public $daddy;

	/**
	 * @Orm:OneToMany(targetEntity="RelatedEntity", mappedBy="daddy", cascade={"persist"})
	 * @var \Kdyby\Tests\Doctrine\Forms\Fixtures\RelatedEntity[]
	 */
	public $children;

	/**
	 * @Orm:ManyToMany(targetEntity="RelatedEntity", inversedBy="buddies", cascade={"persist"})
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
