<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\SvgRenderer;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SvgStorage extends Nette\Object
{

	/**
	 * @var string
	 */
	private $cacheDir;



	/**
	 * @param string $cacheDir
	 * @param string $namespace
	 */
	public function __construct($cacheDir, $namespace = 'Kdyby.Svg')
	{
		if (!is_dir($dir = $cacheDir . '/' . $namespace)) {
			umask(0);
			@mkdir($dir, 0777);
		}

		$this->cacheDir = $dir;
	}



	/**
	 * @return string
	 */
	public function getDir()
	{
		return $this->cacheDir;
	}



	/**
	 * @param string $filename
	 * @param string $content
	 */
	public function save($content, $filename = NULL)
	{
		file_put_contents($this->cacheDir . '/' . $filename, $content);
	}



	/**
	 * @return string
	 */
	public function tempFile()
	{
		$file = tempnam($this->cacheDir, 'tmp_image_');
		register_shutdown_function(function () use ($file) {
			@unlink($file);
		});
		return $file;
	}




	/**
	 * @param string $filename
	 * @return string|bool
	 */
	public function find($filename)
	{
		$results = iterator_to_array(Nette\Utils\Finder::findFiles($filename)->in($this->cacheDir));
		return reset($results);
	}

}
