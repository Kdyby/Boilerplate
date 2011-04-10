<?php

namespace Kdyby\Types;

use Kdyby;
use Nette;
use SplFileInfo;



/**
 * @author Filip Procházka
 *
 * @MappedSuperclass
 */
class File extends Nette\Object
{

	/** @Column(type="string") @var string */
	private $filename;

	/** @Column(type="string") @var string */
	private $dir;

	/** @var SplFileInfo */
	private $info;



	/**
	 * @param string $dir
	 * @param string $filename
	 */
	public function __construct($dir, $filename)
	{
		$this->dir = $dir;
		$this->filename = $filename;

		$targetDir = dirname($this->dir . '/' . $this->filename);
		if (!realpath($targetDir)) {
			@mkdir($targetDir, 0777, TRUE);
			@chmod($targetDir, 0777);
		}
	}



	/**
	 * @return SplFileInfo
	 */
	public function getFileInfo()
	{
		if ($this->info === NULL) {
			$this->info = new \SplFileInfo($this->dir . '/' . $this->filename);
		}

		return $this->info;
	}



	/**
	 * @return string
	 */
	public function getMimeType()
	{
		return Nette\MimeTypeDetector::fromFile($this->dir . '/' . $this->filename);
	}



	/**
	 * @return string
	 */
	public function getContents()
	{
		$contents = @file_get_contents($this->getFileInfo()->getPathname());
		if ($contents === FALSE) {
			throw new \IOException("Reading from " . $this->filename . " failed.");
		}

		return $contents;
	}



	/**
	 * @param string $contents
	 * @param int $flags
	 */
	public function setContents($contents, $flags = 0)
	{
		if (!file_put_contents($this->getFileInfo()->getPathname(), $contents, $flags)) {
			throw new \IOException("Writting to " . $this->filename . " failed.");
		}
	}

}