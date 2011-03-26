<?php

namespace Kdyby\Components\Navigation\Entities;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;



/**
 * @Entity
 */
class NavigationRegistryService extends NavigationRegistry
{

	/** @Column(type="string") @var string */
	private $serviceName;

	/** @Column(type="string") @var string */
	private $methodName;

	/** @Column(type="array", nullable=true) @var array */
	private $serviceArgs;



	/**
	 * @return string
	 */
	public function getServiceName()
	{
		return $this->serviceName;
	}



	/**
	 * @param string $serviceName
	 */
	public function setServiceName($serviceName)
	{
		$this->serviceName = $serviceName;
	}



	/**
	 * @return string
	 */
	public function getMethodName()
	{
		return $this->methodName;
	}



	/**
	 * @param string $methodName
	 */
	public function setMethodName($methodName)
	{
		$this->methodName = $methodName;
	}



	/**
	 * @return array
	 */
	public function getServiceArgs()
	{
		return $this->serviceArgs ?: array();
	}



	/**
	 * @param array $args
	 */
	public function setServiceArgs(array $args)
	{
		$this->serviceArgs = $args ?: NULL;
	}

}