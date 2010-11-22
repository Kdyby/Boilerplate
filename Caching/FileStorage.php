<?php

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
