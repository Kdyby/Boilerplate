<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Caching;

use Nette;



class FileStorage extends Nette\Caching\FileStorage
{

	/**
	 * @param string $key
	 * @return \DateTime
	 */
    public function getCreateTime($key)
    {
        return new \DateTime(filemtime($this->getCacheFile($key)));
    }

}