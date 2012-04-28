<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tools;

use Kdyby;
use Nette;
use Nette\Reflection\Method;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MethodAppend extends Nette\Object
{

	const CHUNK_LENGTH = 5242880;

	/** @var \Nette\Reflection\Method */
	private $method;

	/** @var int */
	private $endLine;



	/**
	 * @param \Nette\Reflection\Method $method
	 */
	public function __construct(Method $method)
	{
		$this->method = $method;
		$this->endLine = $method->getEndLine();
	}



	/**
	 * @param string $code
	 */
	public function append($code)
	{
		// open file
		$resource = fopen($file = $this->method->getFilename(), 'rb+');
		if (!$line = $this->seekEndLine($resource)) {
			fclose($resource);
			throw new Kdyby\IOException("End line of $this->method not found.");
		}

		// content = before bracket + indented content + bracket + after bracket
		$bracketPos = strpos($line, '}');
		$code = Nette\Utils\Strings::indent($code, 2);
		$content = rtrim(substr($line, 0, $bracketPos - 1)) . "\n" . $code . "\n\t" . substr($line, $bracketPos);

		// current position
		$overwritePos = ftell($resource);

		// move content of the file, starting at position, by offset
		$this->moveContent($resource, strlen($content) - strlen($line), $overwritePos);

		// finally overwrite
		fseek($resource, $overwritePos);
		fwrite($resource, $content);
		fclose($resource);

		// mark new position of end line
		$this->endLine += substr_count($code, "\n") + 2;
	}



	/**
	 * @param resource $resource
	 * @param int $offset
	 * @param int $from
	 *
	 * @return mixed
	 */
	private function moveContent($resource, $offset, $from)
	{
		fseek($resource, 0, SEEK_END);
		$length = ($end = ftell($resource)) - $from;
		if (($chunkCount = $length / static::CHUNK_LENGTH) < 1) {
			fseek($resource, $from);
			$chunk = fread($resource, $length);
			fseek($resource, $from + $offset);
			fwrite($resource, $chunk);
			return;
		}

		for ($i = 1; $i <= ($chunks = ceil($chunkCount)); $i++) {
			$currentChunkLength = $i === $chunks ? $length % static::CHUNK_LENGTH : static::CHUNK_LENGTH;
			fseek($resource, $from + ($i === $chunks ? 0 : $length - $i * static::CHUNK_LENGTH));
			$chunk = fread($resource, $currentChunkLength);
			fseek($resource, $from + $offset + ($length - ($i + 1) * static::CHUNK_LENGTH) + $currentChunkLength);
			fwrite($resource, $chunk);
		}
	}



	/**
	 * @param resource $resource
	 *
	 * @return string|FALSE
	 */
	private function seekEndLine($resource)
	{
		rewind($resource);
		for ($i = 1; !feof($resource); $i++) {
			$line = fgets($resource);
			if ($i === $this->endLine) {
				fseek($resource, -strlen($line), SEEK_CUR);
				return $line;
			}
		}
		return FALSE;
	}

}
