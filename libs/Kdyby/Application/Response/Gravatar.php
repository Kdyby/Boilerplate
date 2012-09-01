<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Application\Response;

use Nette;
use Nette\Image;
use Nette\Application\IResponse;
use Nette\Environment;



/**
 * Gravatar image response
 *
 * @author Mikulas Dite, Martin Sadovy
 */
class Gravatar extends Nette\Object implements IResponse
{

	/** @var string uri */
	const URI = 'http://www.gravatar.com/avatar/';

	/** @var int */
	const EXPIRATION = 172800; // two days



	/** @var string|Image */
	private $image;

	/** @var string */
	private $type;



	/**
	 * @param string email
	 * @param int size 1-512
	 */
	public function __construct($email, $size)
	{
		if ((int) $size < 1 || (int) $size > 512) {
			throw new Kdyby\InvalidArgumentException('Unsupported size `' . $size . '`, Gravatar API expects `1 - 512`.');
		}


		$arguments = array(
			's' => (int) $size, // size
			'd' => 'mm', // default image
			'r' => 'g', // inclusive rating
		);

		$hash = md5(strtolower(trim($email)));

		$file = TEMP_DIR . '/cache/gravatar/' . $hash . '_' . $size . '.jpeg';
		if (!file_exists($file) || filemtime($file) < time() - self::EXPIRATION) {

			if (!file_exists(TEMP_DIR . '/cache/gravatar')) {
				mkdir(TEMP_DIR . '/cache/gravatar');
			}

			$query = http_build_query($arguments);
			$img = @file_get_contents(self::URI . $hash . '?' . $query);

			if ($img != NULL) {
				file_put_contents($file, $img);
			}
		}

		$this->image = Image::fromFile($file);
		$this->type = Image::JPEG;
	}



	/**
	 * Returns the path to a file or Nette\Image instance.
	 * @return string|Image
	 */
	final public function getImage()
	{
		return $this->image;
	}



	/**
	 * Returns the type of a image.
	 * @return string
	 */
	final public function getType()
	{
		return $this->type;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		echo $this->image->send($this->type, 85);
	}
}
