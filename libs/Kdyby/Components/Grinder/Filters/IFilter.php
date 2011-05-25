<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Filters;



/**
 * @author Filip Procházka
 */
interface IFilter
{

	public function getName();

	public function getColumn();

	public function createFragments();

}