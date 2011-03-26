<?php

namespace Kdyby\Components\Navigation\Entities;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;



/**
 * @Table(name="application_navigation_items")
 * @Entity(repositoryClass="Kdyby\Doctrine\Repositories\NestedTreeRepository")
 *
 * @property string $name
 * @property string $destination
 * @property array $defaultParams
 * @property-read NavigationNode $parent
 * @property-read Doctrine\Common\Collections\Collection $children
 */
class NavigationNode extends Kdyby\Doctrine\Entities\NestedNode
{

	/**
	 * @gedmo:TreeParent
	 * @ManyToOne(targetEntity="Kdyby\Components\Navigation\Entities\NavigationNode", inversedBy="children")
	 */
	private $parent;

	/**
	 * @var Doctrine\Common\Collections\Collection
	 *
	 * @OneToMany(targetEntity="Kdyby\Components\Navigation\Entities\NavigationNode", mappedBy="parent")
	 * @OrderBy({"nodeLft" = "ASC"})
	 */
	private $children;

	/** @Column(type="string") @var string */
	private $name;

	/** @Column(type="string") @var string */
	private $destination;

	/** @Column(type="array", nullable=TRUE) @var array */
	private $defaultParams = array();



	/**
	 * @return void
	 */
	public function __construct()
	{
		$this->children = new ArrayCollection();
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}



	/**
	 * @return string
	 */
	public function getDestination()
	{
		return $this->destination;
	}



	/**
	 * @param string $destination
	 */
	public function setDestination($destination)
	{
		$this->destination = $destination;
	}



	/**
	 * @return array
	 */
	public function getDefaultParams()
	{
		return $this->defaultParams;
	}



	/**
	 * @param array $defaultParams
	 */
	public function setDefaultParams(array $defaultParams)
	{
		$this->defaultParams = $defaultParams;
	}




//	/**
//	 * @param Sitemap $parent
//	 */
//	public function setParent(Sitemap $parent)
//	{
//		$this->parent = $parent;
//	}



	/**
	 * @return self|Null
	 */
    public function getParent()
	{
		return $this->parent;
	}



	/**
	 * @param Sitemap $sitemap
	 */
	public function addChild(Sitemap $sitemap)
	{
		$sitemap->setParent($this);
		$this->children->add($sitemap);
	}



	/**
	 * @return array of self
	 */
    public function getChildren()
	{
		return $this->children;
	}

}