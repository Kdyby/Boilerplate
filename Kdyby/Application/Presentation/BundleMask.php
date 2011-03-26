<?php

namespace Kdyby\Application\Presentation;

use Kdyby;
use Nette;



/**
 * @Entity(repositoryClass="Kdyby\Application\Presentation\BundleMaskRepository")
 * @Table(name="application_bundle_mask")
 */
class BundleMask extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @Column(type="string", unique=TRUE) @var string  */
	private $mask;

	/** @ManyToOne(targetEntity="Bundle", inversedBy="masks") @var Bundle */
	private $bundle;

	/**
	 * Equals to number of specific sequences in mask
	 * this could be computed, but it's better this way
	 *
	 * @Column(type="integer")
	 * @var int
	 */
	private $clarity;



	public function getMask()
	{
		return $this->mask;
	}



	public function setMask($mask)
	{
		$this->mask = $mask;
	}



	/**
	 * @return Bundle
	 */
	public function getBundle()
	{
		return $this->bundle;
	}



	public function setBundle(Bundle $bundle)
	{
		$this->bundle = $bundle;
	}



	public function getClarity()
	{
		return $this->clarity;
	}



	public function setClarity($clarity)
	{
		$this->clarity = $clarity;
	}

}