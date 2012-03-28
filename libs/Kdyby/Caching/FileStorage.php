<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Caching;

use DateTime;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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
