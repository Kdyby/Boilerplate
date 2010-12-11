<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Location;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity @Table(name="location_addresses")
 */
class Address extends Kdyby\Entities\BaseIdentifiedEntity
{
	/** @Column(type="string", length=100, nullable=TRUE) */
	private $street;

	/** @Column(type="integer", length=20, nullable=TRUE) */
	private $number;

	/** @Column(type="integer", length=20, nullable=TRUE) */
	private $orientationNumber;

	/**
	 * @OneToOne(targetEntity="Kdyby\Location\City")
     * @JoinColumn(name="city_id", referencedColumnName="id")
	 */
	private $city;

	/** @Column(type="string", nullable=TRUE) */
	private $citypart;

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

	/** @Column(type="integer", length=7, nullable=TRUE) */
	private $zip;

	/**
	 * Územně identifikační registr adres - oficialni seznam všech existujících adress v ČR
	 * UIR lokality
	 * @Column(type="string", nullable=TRUE)
	 */
	private $uir;

	/**
	 * UIR-level lokality
	 * @Column(type="string", nullable=TRUE)
	 */
	private $uirLevel;

	/**
	 * Zeměpisná šířka
	 * @Column(type="string", nullable=TRUE)
	 */
	private $latitude;

	/**
	 * Zeměpisná délka
	 * @Column(type="string", nullable=TRUE)
	 */
	private $longitude;



	public function getStreet() { return $this->street; }
	public function setStreet($street) { $this->street = $street; }

	public function getNumber() { return $this->number; }
	public function setNumber($number) { $this->number = $number; }

	public function getOrientationNumber() { return $this->orientationNumber; }
	public function setOrientationNumber($orientationNumber) { $this->orientationNumber = $orientationNumber; }

	public function getCity() { return $this->city; }
	public function setCity(City $city) { $this->city = $city; }

	public function getCitypart() { return $this->citypart; }
	public function setCitypart($citypart) { $this->citypart = $citypart; }

	public function getDistrict() { return $this->district; }
	public function setDistrict(District $district) { $this->district = $district; }

	public function getState() { return $this->state; }
	public function setState(State $state) { $this->state = $state; }

	public function getZip() { return $this->zip; }
	public function setZip($zip) { $this->zip = $zip; }

	public function getUir() { return $this->uir; }	
	public function setUir($uir) { $this->uir = $uir; }

	public function getUirLevel() { return $this->uirLevel; }
	public function setUirLevel($uirLevel) { $this->uirLevel = $uirLevel; }

	public function getLatitude() { return $this->latitude; }
	public function setLatitude($latitude) { $this->latitude = $latitude; }

	public function getLongtitude() { return $this->longitude; }
	public function setLongtitude($longtitude) { $this->longitude = $longtitude; }

}