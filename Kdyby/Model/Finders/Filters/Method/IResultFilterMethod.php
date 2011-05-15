<?php

namespace Kdyby\Model\Finders\Filters\Method;

use Doctrine;



interface IResultFilterMethod
{

	/**
	 * Build a single, generic query part.
	 *
	 * @param array $values
	 * @param array $fields
	 * @return Doctrine\ORM\Query\Expr
	 */
	function buildFragment(array $values, array $fields);

}