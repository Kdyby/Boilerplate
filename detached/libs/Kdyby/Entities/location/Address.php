<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Location;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity @Table(name="location_addresses")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="_type", type="integer")
 * @DiscriminatorMap({
 *		1 = "Kdyby\Location\Address",
 *		2 = "Kdyby\Location\PreciseAddress",
 *		3 = "Kdyby\Location\BillingAddress",
 *		4 = "Kdyby\Location\ContactAddress"
 *	})
 */
class Address extends Kdyby\Doctrine\IdentifiedEntity
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

}