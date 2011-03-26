<?php

namespace Kdyby\Application\Presentation;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;



/**
 * @Entity
 * @Table(name="application_bundle")
 */
class Bundle extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @Column(type="string") @var string  */
	private $name;

	/** @OneToMany(targetEntity="BundleMask", mappedBy="bundle") @var Doctrine\Common\Collections\Collection */
	private $masks;

	/** @OneToOne(targetEntity="Sitemap") @var Sitemap */
	private $sitemap;

	/** @Column(type="string") @var string */
	private $placeholderName;

	/** @Column(type="boolean") @var bool */
	private $multilingual = FALSE;

	/** @Column(type="boolean") @var bool */
	private $locked = FALSE;

	/** @Column(type="boolean") @var bool */
	private $private = TRUE;



	public function __construct()
	{
		$this->masks = new ArrayCollection;
	}



	public function getName()
	{
		return $this->name;
	}



	public function setName($name)
	{
		$this->name = $name;
	}



	public function getMultilingual()
	{
		return $this->multilingual;
	}



	public function setMultilingual($multilingual)
	{
		$this->multilingual = $multilingual;
	}



	/**
	 * @return Sitemap
	 */
	public function getSitemap() 
	{
		return $this->sitemap;
	}



	/**
	 * @param Sitemap $sitemap
	 */
	public function setSitemap(Sitemap $sitemap)
	{
		$this->sitemap = $sitemap;
		$sitemap->setBundle($this);
	}



	/**
	 * @return string
	 */
	public function getPlaceholderName()
	{
		return $this->placeholderName;
	}



	/**
	 * @param string $placeholderName
	 */
	public function setPlaceholderName($placeholderName)
	{
		$this->placeholderName = $placeholderName;
	}



	/**
	 * @return bool
	 */
	public function getLocked() 
	{
		return $this->locked;
	}



	/**
	 * @param bool $locked
	 */
	public function setLocked($locked)
	{
		$this->locked = (bool)$locked;
	}



	/**
	 * @return bool
	 */
	public function getPrivate() 
	{
		return $this->private;
	}



	/**
	 * @param bool $private
	 */
	public function setPrivate($private)
	{
		$this->private = (bool)$private;
	}

}