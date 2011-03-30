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
	private $defaultParams;

	/** @Column(type="array", nullable=TRUE) @var array */
	private $requiredParams;

	/** @Column(type="array", nullable=TRUE) @var array */
	private $mapSequence;



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
		return (array)$this->defaultParams;
	}



	/**
	 * @param array $defaultParams
	 */
	public function setDefaultParams(array $defaultParams)
	{
		$this->defaultParams = $defaultParams ?: NULL;
	}



	public function getRequiredParams()
	{
		return (array)$this->requiredParams;
	}



	public function setRequiredParams(array $requiredParams)
	{
		$this->requiredParams = $requiredParams ?: NULL;
	}



	public function getMapSequence()
	{
		return (array)$this->mapSequence;
	}



	public function setMapSequence(array $mapSequence)
	{
		$this->mapSequence = $mapSequence ?: NULL;
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
	 * @param array $sequences
	 * @return Sitemap
	 */
	public function getChildrenBySequences(array $sequences)
	{
		$path = $this->getChildrensPathBySequences($sequences);
		return end($path);
	}



	/**
	 * @param array $sequences
	 * @return Sitemap[]
	 */
	public function getChildrensPathBySequences(array $sequences)
	{
		$sequence = array_shift($sequences);

		$child = $this->getChildren()->filter(function(Sitemap $sitemap) use ($sequence) {
			return $sitemap->sequence === $sequence;
		})->current();

		if ($child) {
			$children = array($child);
			if ($sequences) {
				$nextChildren = $child->getChildrensPathBySequences($sequences);
				if ($nextChildren) {
					$children = array_merge($children, $nextChildren);
				}
			}

			return $children;
		}

		return NULL;
	}



	/**
	 * @return array
	 */
	public function getSequencePathUp()
	{
		return $this->getParent()
			? array_merge($this->getParent()->getSequencePathUp(), array($this->getSequence()))
			: array($this->getSequence());
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