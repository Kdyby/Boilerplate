<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine\Common\Collections\Collection;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Helpers extends Nette\Object
{

	/**
	 * @param \Doctrine\Common\Collections\Collection $col
	 * @param object|integer $element
	 * @param string $primary
	 *
	 * @return bool
	 */
	public static function collectionRemove(Collection $col, $element, $primary = 'id')
	{
		if (is_object($element)) {
			return $col->removeElement($element);
		}

		$removed = FALSE;
		foreach ($col as $item) {
			if ($item->{'get' . ucFirst($primary)}() === $element) {
				$col->remove($item);
				$removed = TRUE;
			}
		}

		return $removed;
	}



	/**
	 * @param \Doctrine\Common\Collections\Collection $col
	 * @param object|integer $element
	 * @param string $primary
	 *
	 * @return bool
	 */
	public static function collectionHas(Collection $col, $element, $primary = 'id')
	{
		if (is_object($element)) {
			return $col->contains($element);
		}

		foreach ($col as $item) {
			if ($item->{'get' . ucFirst($primary)}() === $element) {
				return TRUE;
			}
		}

		return FALSE;
	}

}
