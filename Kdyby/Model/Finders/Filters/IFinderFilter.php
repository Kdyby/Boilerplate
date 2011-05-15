<?php

namespace Kdyby\Model\Finders\Filters;

use Kdyby;
use Nette;



interface IFinderFilter
{

	/**
	 * @return string
	 */
	function getName();


	/**
	 * @param bool $independent
	 */
	function setIndependent($independent = TRUE);


	/**
	 * @return bool
	 */
	function isIndependent();


	/**
	 * @param array|string $state
	 */
	function loadState($state);


	/**
	 * @return array|string
	 */
	function saveState();


	/**
	 * @param array|string $newState
	 * @return bool
	 */
	function handleChangeState($newState);


	/**
	 * @return array|Doctrine\ORM\Query\Expr
	 */
	function buildFragment();

}