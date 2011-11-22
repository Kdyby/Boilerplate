<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Kdyby\Tools\FileSystem;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FileSystemTest extends Kdyby\Tests\TestCase
{

	public function setUp()
	{
		@unlink(APP_DIR . '/log/symlink');
		if (!symlink(KDYBY_FRAMEWORK_DIR . '/loader.php', APP_DIR . '/log/symlink')) {
			throw new Nette\IOException("Test suit cannot create symbolic link for testing.");
		}
	}



	/**
	 * @return array
	 */
	public function getPaths()
	{
		return array(
			array('.'),
			array('./'),
			array(__DIR__ . '/./' . basename(__FILE__)),
			array(__DIR__ . '/./../Tools/' . basename(__FILE__)),
			array(APP_DIR . '/log/../log/./symlink'),
		);
	}



	/**
	 * @dataProvider getPaths
	 */
	public function testRealpath($path)
	{
		$this->assertSame(realpath($path), FileSystem::realPath($path), 'Path ' . $path);
		$this->assertInternalType('string', FileSystem::realPath($path));
	}



	/**
	 * @return array
	 */
	public function getPathsWithPreservedSymlinks()
	{
		return array(
			array(__DIR__ . '/.', __DIR__),
			array(__DIR__ . '/./', __DIR__),
			array(__DIR__ . '/./' . basename(__FILE__), __DIR__ . '/' . basename(__FILE__)),
			array(__DIR__ . '/./../Tools/' . basename(__FILE__), __DIR__ . '/' . basename(__FILE__)),
			array(APP_DIR . '/log/../log/./symlink', APP_DIR . '/log/symlink'),
		);
	}



	/**
	 * @dataProvider getPathsWithPreservedSymlinks
	 */
	public function testRealpathWithPreservedSymlinks($path, $canonicalized)
	{
		$this->assertSame($canonicalized, FileSystem::realPath($path, TRUE), 'Path ' . $path);
		$this->assertInternalType('string', FileSystem::realPath($path, TRUE));
	}

}