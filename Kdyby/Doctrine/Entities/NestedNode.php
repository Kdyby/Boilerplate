<?php

namespace Kdyby\Doctrine\Entities;

use Gedmo;
use Kdyby;
use Nette;



/**
 * In descendants requires to set Entity annotation like:
 * Entity(repositoryClass="Kdyby\Doctrine\Repositories\NestedTreeRepository")
 *
 * @MappedSuperclass
 * @gedmo:Tree(type="nested")
 */
abstract class NestedNode extends IdentifiedEntity implements Gedmo\Tree\Node
{

    /**
     * @gedmo:TreeLeft
     * @Column(name="node_lft", type="integer")
     */
    private $nodeLft;

    /**
     * @gedmo:TreeLevel
     * @Column(name="node_lvl", type="integer")
     */
    private $nodeLvl;

    /**
     * @gedmo:TreeRight
     * @Column(name="node_rgt", type="integer")
     */
    private $nodeRgt;

    /**
     * @gedmo:TreeRoot
     * @Column(name="node_root", type="integer", nullable=true)
     */
    private $nodeRoot = 0;

//	/**
//	 * @gedmo:TreeParent
//	 * @ManyToOne(targetEntity="Category", inversedBy="children")
//	 */
//	abstract private $parent;

//	/**
//	 * @OneToMany(targetEntity="Category", mappedBy="parent")
//	 * @OrderBy({"lft" = "ASC"})
//	 */
//	abstract private $children;

	/** @Column(type="boolean") @var bool */
	private $useRoot = FALSE;



	/**
	 * @return self|Null
	 */
    public function isRoot()
	{
		return (bool)$this->nodeRoot;
	}



	/**
	 * @return self|Null
	 */
    abstract public function getParent();



	/**
	 * @return array of self
	 */
    abstract public function getChildren();



	/**
	 * @return bool
	 */
	public function getUseRoot()
	{
		return $this->useRoot;
	}



	/**
	 * @param bool $useRoot
	 */
	public function setUseRoot($useRoot)
	{
		if (!$this->isRoot()) {
			throw new \InvalidStateException("Whether or not to 'use root' can be set only on root node.");
		}

		$this->useRoot = (bool)$useRoot;
	}
	
}