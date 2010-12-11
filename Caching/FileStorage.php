<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby;

use Nette;



/**
 * Description of FileStorage
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class FileStorage extends Nette\Caching\FileStorage
{

    public function getCreateTime($key)
    {
        return new \DateTime(filemtime($this->getCacheFile($key)));
    }

}
