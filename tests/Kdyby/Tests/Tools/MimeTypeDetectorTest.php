<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Kdyby\Tools\MimeTypeDetector;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MimeTypeDetectorTest extends Kdyby\Tests\TestCase
{

	public function testExtensionToMime()
	{
		$this->assertEquals('image/jpeg', MimeTypeDetector::extensionToMime('jpg'));
	}



	public function testExtensionToMime_NotFound()
	{
		$this->assertNull(MimeTypeDetector::extensionToMime('fuuuuu', FALSE));
	}



	/**
	 * @expectedException Kdyby\InvalidArgumentException
	 */
	public function testExtensionToMime_NotFoundException()
	{
		MimeTypeDetector::extensionToMime('fuuuuu');
	}



	public function testMimeToExtension()
	{
		$this->assertEquals('jpg', MimeTypeDetector::mimeToExtension('image/jpeg'));
	}



	public function testMimeToExtension_NotFound()
	{
		$this->assertNull(MimeTypeDetector::mimeToExtension('fuuuu/fuuuuuu', FALSE));
	}



	/**
	 * @expectedException Kdyby\InvalidArgumentException
	 */
	public function testMimeToExtension_NotFoundException()
	{
		MimeTypeDetector::mimeToExtension('fuuuu/fuuuuuu');
	}

}
