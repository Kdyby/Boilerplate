<?php

namespace Kdyby\Application\Presentation;

use Nette;
use Kdyby;



/**
 * Describes how to assemble webpage
 */
final class PresentationRequest extends Nette\Object //implements IPresentationRequest
{

	private $containers = array();



	public function addContainer($name)
	{

	}



	public function getContainer($name)
	{
		
	}



	public function getContainers()
	{

	}



	public function addComponent($container, IComponent $component)
	{
		$this->getContainer($name)->addComponent($component);
	}

}