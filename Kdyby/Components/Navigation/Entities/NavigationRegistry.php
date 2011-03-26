<?php

namespace Kdyby\Components\Navigation\Entities;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;



/**
 * @Entity
 * @Table(name="application_navigation_registry")
 *
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="_type", type="integer")
 * @DiscriminatorMap({
 *		1 = "Kdyby\Components\Navigation\Entities\NavigationRegistryEntity",
 *		2 = "Kdyby\Components\Navigation\Entities\NavigationRegistryCallback",
 *		3 = "Kdyby\Components\Navigation\Entities\NavigationRegistryService"
 *  })
 */
abstract class NavigationRegistry extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @Column(type="string") @var string */
	private $name;

}