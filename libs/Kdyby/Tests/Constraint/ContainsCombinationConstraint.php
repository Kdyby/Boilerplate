<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Constraint;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ContainsCombinationConstraint extends \PHPUnit_Framework_Constraint
{

	/**
	 * @var boolean
	 */
	public $allowOnlyMentioned = TRUE;

	/**
	 * @var boolean
	 */
	public $allowDuplications = FALSE;


	/**
	 * @var array
	 */
	protected $lists;

	/**
	 * @var array
	 */
	protected $mappers;



	/**
	 * @param array $lists
	 * @param array $mappers
	 */
	public function __construct(array $lists, array $mappers)
	{
		if (count($lists) !== count($mappers)) {
			throw new Kdyby\InvalidArgumentException("Count of given lists does not equals to count of given mappers.");
		}

		$this->lists = $lists;
		$this->mappers = array_map('callback', $mappers);
	}



	/**
	 * @param $collection
	 *
	 * @return bool
	 */
	protected function matches($collection)
	{
		$valueCounts = $keys = array();
		foreach ($collection as $item) {
			foreach ($this->mappers as $i => $mapper) {
				$keys[$i] = $mapper($item);
			}
			Kdyby\Tools\Arrays::callOnRef($valueCounts, $keys, function (&$value) { $value += 1; });
		}

		if ($this->allowDuplications === FALSE) {
			$counts = array_values(array_unique(Kdyby\Tools\Arrays::flatMap($valueCounts)));
			if ($counts !== array(1)) {
				$this->fail($collection, "Collection contains duplications");
			}
		}

		$lists = $this->lists;
		$foundLists = array_fill(0, count($lists), array());
		$inList = Kdyby\Tools\Arrays::flatMapAssoc($valueCounts, function ($value, $keys) use ($lists, &$foundLists) {
			$return = TRUE;
			foreach ($keys as $i => $key) {
				$foundLists[$i][] = $key;
				if (!in_array($key, $lists[$i])) {
					$return = $keys;
				}
			}
			return $return;
		});

		if ($this->allowOnlyMentioned === TRUE) {
			$notMentioned = array_filter($inList, function ($isIn) {
				return $isIn !== TRUE;
			});
			$literal = $notMentioned ? "'" . implode(', ', current($notMentioned)) . "'" : NULL;
			if (!empty($notMentioned)) {
				$this->fail($collection, "The collection contains combination " . $literal . ", that cannot be assembled from given lists . ");
			}
		}

		foreach ($foundLists as $i => $list) {
			$diff = array_diff($lists[$i], $list);
			$literal = $diff ? "'" . implode(', ', $diff) . "'" : NULL;
			if (!empty($diff)) {
				$this->fail($collection, "There are all given values " . $literal . " in collection");
			}
		}

		return TRUE;
	}



	/**
	 * Returns a string representation of the constraint.
	 *
	 * @return string
	 */
	public function toString()
	{
		return 'Object does not contain given combinations';
	}

}
