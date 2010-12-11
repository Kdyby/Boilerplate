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
use Kdyby;


/**
 * Description of Images
 *
 * <code type="config/apache">
 * RewriteEngine On
 *
 * RewriteCond %{REQUEST_URI} ^/previews/([^.]+)\.(jpg|png|gif)$
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteRule (.*) /images/preview?file=%1.%2 [NE,L]
 * </code>
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Images extends Nette\Object
{

	public function lazyPreview($source,$target)
	{
		// save as '/images/2010/11/fd/fds654ds3as8fd.800x600.jpg'
	}

}
