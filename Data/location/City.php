<?php

namespace Kdyby\Location;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity @Table(name="location_cities")
 */
class City extends Kdyby\Entities\BaseIdentifiedEntity
{
	/** @Column(type="string", length=100) */
	private $name;

	/**
	 * @ManyToOne(targetEntity="Kdyby\Location\District", inversedBy="cities")
     * @JoinColumn(name="district_id", referencedColumnName="id")
	 */
	private $district;

	/**
	 * @ManyToOne(targetEntity="Kdyby\Location\State", inversedBy="cities")
     * @JoinColumn(name="state_id", referencedColumnName="id")
	 */
	private $state;

	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; }

	public function getDistrict() { return $this->district; }
	public function setDistrict(District $district) { $this->district = $district; }

	public function getState() { return $this->state; }
	public function setState(State $state) { $this->state = $state; }

}