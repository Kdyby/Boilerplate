<?php

namespace Kdyby;

use Nette;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class FileService extends Nette\Object
{


	/**
	 * @param string $dir
	 * @return string
	 */
	public static function prepareDir($dir)
	{
		umask(0000);
		@mkdir($dir, 0755); // @ - directory may exists

		return $dir;
	}

}
