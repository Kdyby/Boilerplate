<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\SvgRenderer;

use DOMXPath;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class InkscapeRenderer extends Nette\Object implements IRenderer
{

	/**
	 * @var SvgStorage
	 */
	private $storage;



	/**
	 * @param DI\Configuration $config
	 * @param SvgStorage $storage
	 */
	public function __construct(DI\Configuration $config, SvgStorage $storage)
	{
		$this->storage = $storage;
	}



	/**
	 * @param SvgImage $svg
	 * @throws IOException
	 * @return string
	 */
	public function render(SvgImage $svg)
	{
		$dom = $svg->getDocument();

		set_error_handler(function ($code, $message, $file, $line, array $context) {
			throw new IOException($message, $code, new \ErrorException($message, 0, $code, $file, $line));
		});

		$path = new DOMXPath($dom);
		foreach ($path->query('//*') as $node) {
			/** @var \DOMElement $node */

			if (strtolower($node->tagName) === 'image') {
				try {
					$href = $node->getAttribute('xlink:href');
					if (strpos($href, 'http://') === 0 || strpos($href, 'https://') === 0) {
						$image = file_get_contents($href);
						$imagePath = $this->storage->save($image, md5($href) . '.' . strlen($image) . '.' . pathinfo(basename($href), PATHINFO_EXTENSION));
						$node->setAttribute('xlink:href', $this->storage->getDir() . '/' . $imagePath);
					}

				} catch (IOException $e) {
					// inkscape will render "image not found"
					// $node->parentNode->removeChild($node);
				}
			}
		}

		restore_error_handler();

		$source = $this->storage->tempFile($dom->saveXML(), FALSE);
		$target = $this->storage->tempFile(NULL, FALSE);

		$process = new InkscapeProcess(array(
			'--file' => $source,
			'--export-png' => $target,
			'--without-gui'
		));
		$process->execute();

		return file_get_contents($target);
	}

}
