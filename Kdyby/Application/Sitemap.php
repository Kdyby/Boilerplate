<?php

namespace Kdyby\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;



/**
 * @Table(name="application_sitemap")
 * @Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 */
class Sitemap extends Kdyby\Doctrine\Entities\NestedNode
{

	/**
	 * @gedmo:TreeParent
	 * @ManyToOne(targetEntity="Sitemap", inversedBy="children")
	 */
	private $parent;

	/**
	 * @OneToMany(targetEntity="Sitemap", mappedBy="parent")
	 * @OrderBy({"lft" = "ASC"})
	 */
	private $children;



	public function __construct()
	{
		$this->children = new ArrayCollection();
	}



	/**
	 * @param Sitemap $parent
	 */
	public function setParent(Sitemap $parent)
	{
		$this->parent = $parent;
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
		$sitemap->setParent($this);
		dump(get_class($this->children));
//		$this->children[] = $sitemap; // ORLY?
	}



	/**
	 * @return array of self
	 */
    public function getChildren()
	{
		return $this->children;
	}

}