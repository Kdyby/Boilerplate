<?php

namespace Kdyby\Location;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity
 */
class PreciseAddress extends Address
{

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



	public function getUir() { return $this->uir; }
	public function setUir($uir) { $this->uir = $uir; }

	public function getUirLevel() { return $this->uirLevel; }
	public function setUirLevel($uirLevel) { $this->uirLevel = $uirLevel; }

	public function getLatitude() { return $this->latitude; }
	public function setLatitude($latitude) { $this->latitude = $latitude; }

	public function getLongtitude() { return $this->longitude; }
	public function setLongtitude($longtitude) { $this->longitude = $longtitude; }

}