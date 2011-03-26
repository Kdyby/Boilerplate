<?php

namespace Kdyby\Components\Navigation\Entities;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;



/**
 * @Entity
 */
class NavigationRegistryCallback extends NavigationRegistry
{

	/** @Column(type="callback") @var Nette\Callback */
	private $callback;

	/** @Column(type="array", nullable=true) @var array */
	private $callbackArgs;



	/**
	 * @return Nette\Callback
	 */
	public function getCallback()
	{
		return $this->callback;
	}



	/**
	 * @param Nette\Callback $callback
	 */
	public function setCallback(Nette\Callback $callback)
	{
		$this->callback = $callback;
	}



	/**
	 * @return array
	 */
	public function getCallbackArgs()
	{
		return $this->callbackArgs ?: array();
	}



	/**
	 * @param array $args
	 */
	public function setCallbackArgs(array $args)
	{
		$this->callbackArgs = $args ?: NULL;
	}

}