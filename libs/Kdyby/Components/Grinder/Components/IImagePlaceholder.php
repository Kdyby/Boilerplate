<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Components;

use Nette;



/**
 * @author Filip Procházka
 */
interface IImagePlaceholder
{

	/**
	 * @param Nette\ComponentModel\IComponent $parent
	 */
	function setParent(Nette\ComponentModel\IComponent $parent);


	/**
	 * @return Nette\Utils\Html
	 */
	function getPrototype();



	/**
	 * @param string|callable $image
	 */
	function setImage($image);



	/**
	 * @return Nette\Utils\Html
	 */
	function getControl();

}