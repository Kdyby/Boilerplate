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
	 * @var array
	 */
	private $cleanup = array();



	/**
	 * @param string $cacheDir
	 * @param string $namespace
	 */
	public function __construct($cacheDir, $namespace = '_Kdyby.Svg')
	{
		if (!is_dir($dir = $cacheDir . '/' . $namespace)) {
			umask(0);
			@mkdir($dir, 0775);
		}

		$this->cacheDir = $dir;

		register_shutdown_function(callback($this, 'clean'));
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
	public function save($content, $filename)
	{
		file_put_contents($this->cacheDir . '/' . $filename, $content);
		return $filename;
	}



	/**
	 * @param string $contents
	 * @param bool $autoRemove
	 * @return string
	 */
	public function tempFile($contents = NULL, $autoRemove = TRUE)
	{
		$file = tempnam($this->cacheDir, 'tmp_image_');
		@chmod($file, 0664);

		if ($contents !== NULL) {
			$this->save($contents, basename($file));
		}

		if ($autoRemove) {
			$this->cleanup[] = $file;
		}

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



	/**
	 * @internal
	 */
	public function clean()
	{
		foreach ($this->cleanup as $file) {
			@unlink($file);
		}
	}

}
