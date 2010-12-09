<?php

namespace Kdyby;

use Nette;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class FileSystem extends Nette\Object
{


	/**
	 * @param string $dir
	 * @return string
	 */
	public static function prepareWritableDir($dir)
	{
		$dir = Nette\Environment::expand($dir);

		umask(0000);
		@mkdir($dir, 0755); // @ - directory may exists

		return $dir;
	}

}
