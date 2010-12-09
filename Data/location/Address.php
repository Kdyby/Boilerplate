<?php

namespace Kdyby\Location;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity @Table(name="location_addresses")
 */
class Address extends Kdyby\Entities\BaseEntity
{
	/** @Column(type="string", length=100) */
	private $street;

	/** @Column(type="integer", length=20) */
	private $number;

	/** @Column(type="integer", length=20) */
	private $orientationNumber;

	/**
	 * @OneToOne(targetEntity="Kdyby\Location\City")
     * @JoinColumn(name="city_id", referencedColumnName="id")
	 */
	private $city;

	/**
	 * @OneToOne(targetEntity="Kdyby\Location\District")
     * @JoinColumn(name="district_id", referencedColumnName="id")
	 */
	private $district;

	/**
	 * @OneToOne(targetEntity="Kdyby\Location\State")
     * @JoinColumn(name="state_id", referencedColumnName="id")
	 */
	private $state;

	/** @Column(type="integer", length=7) */
	private $zip;



	public function getStreet() { return $this->street; }
	public function setStreet($street) { $this->street = $street; }

	public function getNumber() { return $this->number; }
	public function setNumber($number) { $this->number = $number; }

	public function getOrientationNumber() { return $this->orientationNumber; }
	public function setOrientationNumber($orientationNumber) { $this->orientationNumber = $orientationNumber; }

	public function getCity() { return $this->city; }
	public function setCity(City $city) { $this->city = $city; }

	public function getDistrict() { return $this->district; }
	public function setDistrict(District $district) { $this->district = $district; }

	public function getState() { return $this->state; }
	public function setState(State $state) { $this->state = $state; }

	public function getZip() { return $this->zip; }
	public function setZip($zip) { $this->zip = $zip; }

}