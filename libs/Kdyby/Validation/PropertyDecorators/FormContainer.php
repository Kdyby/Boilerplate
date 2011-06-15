<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation\PropertyDecorators;

use Kdyby;
use Kdyby\Validation;
use Nette;
use Nette\Forms\Container;



/**
 * @author Filip Procházka
 */
class FormContainer extends Nette\Object implements Validation\IPropertyDecorator
{

	/** @var Container */
	private $container;



	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}



	/**
	 * @return Container
	 */
	public function getEntity()
	{
		return $this->container;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->container->getName();
	}



	/**
	 * @param string $property
	 * @return mixed
	 */
	public function getValue($property)
	{
		return $this->container->values[$property];
	}



	/**
	 * @param object $entity
	 * @return IPropertyDecorator
	 */
	public function decorate($entity)
	{
		return new static($entity);
	}

}