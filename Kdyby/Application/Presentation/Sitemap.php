<?php

namespace Kdyby\Application\Presentation;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;



/**
 * @Table(name="application_sitemap")
 * @Entity(repositoryClass="Kdyby\Application\Presentation\SitemapRepository")
 */
class Sitemap extends Kdyby\Doctrine\Entities\NestedNode
{

	/**
	 * @gedmo:TreeParent
	 * @ManyToOne(targetEntity="Sitemap", inversedBy="children")
	 */
	private $parent;

	/**
	 * @var Doctrine\Common\Collections\Collection
	 *
	 * @OneToMany(targetEntity="Sitemap", mappedBy="parent")
	 * @OrderBy({"nodeLft" = "ASC"})
	 */
	private $children;

	/** @OneToOne(targetEntity="Bundle") @var Bundle */
	private $bundle;

	/** @Column(type="string") @var string */
	private $name;

	/** @Column(type="string") @var string */
	private $sequence;

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



	public function getSequence()
	{
		return $this->sequence;
	}



	public function setSequence($sequence)
	{
		$this->sequence = $sequence;
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
		$sitemap->parent = $this;
		$this->children->add($sitemap);
	}



	/**
	 * @return array of self
	 */
    public function getChildren()
	{
		return $this->children;
	}



	/**
	 * @return Bundle
	 */
	public function getBundle() 
	{
		return $this->bundle;
	}



	/**
	 * @param Bundle $bundle
	 */
	public function setBundle(Bundle $bundle)
	{
		$this->bundle = $bundle;
	}

}