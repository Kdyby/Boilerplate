<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Caching;

use DateTime;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FileStorage extends Nette\Caching\Storages\FileStorage
{

	/**
	 * @param string $key
	 * @return Nette\DateTime|NULL
	 */
    public function getCreateTime($key)
    {
		return Nette\DateTime::createFromFormat('U', @filemtime($this->getCacheFile($key))) ?: NULL;
    }

}
