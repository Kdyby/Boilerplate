<?php

namespace Kdyby\Location;

use Doctrine\Common\Collections\ArrayCollection;
use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity @Table(name="location_districts")
 */
class District extends Kdyby\Entities\BaseEntity
{

	/** @Column(type="string") */
	private $name;

	/** @OneToMany(targetEntity="Kdyby\Location\City", mappedBy="state") */
	private $cities;

	/**
	 * @ManyToOne(targetEntity="Kdyby\Location\State", inversedBy="districts")
     * @JoinColumn(name="state_id", referencedColumnName="id")
	 */
	private $state;



	public function __construct()
	{
		parent::__construct();

		$this->cities = new ArrayCollection();
	}


	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; }

	public function addCity(City $city)
	{
		$this->cities->add($city);
		$city->setDistrict($this);
	}

	public function removeCity(City $city)
	{
		$this->cities->removeElement($city);
		$city->setDistrict(NULL);
	}

	public function getState() { return $this->state; }
	public function setState(State $state) { $this->state = $state; }

}