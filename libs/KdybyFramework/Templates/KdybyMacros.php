<?php

namespace Kdyby\Templates;

use Nette;
use Kdyby;



class KdybyMacros extends Nette\Object
{

	public static function register()
	{
		Nette\Templates\LatteMacros::$defaultMacros['theme'] = "<?php echo \$presenter->getThemePath(%%); ?>";
	}

}