<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Filters;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
interface IFragmentsBuilder
{

	function buildEquals($value, Filter $filter);
	function buildLike($value, Filter $filter);

	function buildLowerThan($value, Filter $filter);
	function buildLowerOrEqualThan($value, Filter $filter);

	function buildHigherThan($value, Filter $filter);
	function buildHigherOrEqualThan($value, Filter $filter);

	function buildNull($value, Filter $filter);

}