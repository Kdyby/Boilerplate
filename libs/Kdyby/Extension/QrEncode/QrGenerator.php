<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\QrEncode;

use Kdyby;
use Nette\Utils\Strings;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class QrGenerator extends QrOptions implements IGenerator
{

	/**
	 * @param DI\Configuration $config
	 */
	public function __construct(DI\Configuration $config)
	{
		parent::__construct(
			$config->size,
			$config->errorCorrection,
			$config->version,
			$config->margin,
			$config->options
		);
	}



	/**
	 * @param QrCode $qr
	 * @throws ProcessException
	 * @return string
	 */
	public function render(QrCode $qr)
	{
		$process = new QrEncodeProcess($this->buildOptions($qr));
		return $process->execute();
	}



	/**
	 * @param QrCode $qr
	 * @throws IOException
	 * @return array
	 */
	private function buildOptions(QrCode $qr)
	{
		return array(
			'--output=-',
			'--size' => $qr->getSize($this->getSize()),
			'--level' => $qr->getErrorCorrection($this->getErrorCorrection()),
			'--symversion' => $qr->getVersion($this->getVersion()),
			'--margin' => $qr->getMargin($this->getMargin()),
			'--structured' => $qr->hasOption(QrCode::STRUCTURED, $this->getOptions()) ? : NULL,
			'--kanji' => $qr->hasOption(QrCode::KANJI, $this->getOptions()) ? : NULL,
			'--casesensitive' => $qr->hasOption(QrCode::CASE_SENSITIVE, $this->getOptions()) ? : NULL,
			'--ignorecase' => $qr->hasOption(QrCode::CASE_INSENSITIVE, $this->getOptions()) ? : NULL,
			'--8bit' => $qr->hasOption(QrCode::ENCODE_8BIT, $this->getOptions()) ? : NULL,
			'' => $qr->getString()
		);
	}

}
