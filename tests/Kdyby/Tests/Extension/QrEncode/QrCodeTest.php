<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\QrEncode;

use Kdyby;
use Kdyby\Extension\QrEncode\QrCode;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class QrCodeTest extends Kdyby\Tests\TestCase
{

	public function testHasOption_DefaultIsFalse()
	{
		$qr = new QrCode('nemam');
		$this->assertFalse($qr->hasOption(QrCode::STRUCTURED));
		$this->assertFalse($qr->hasOption(QrCode::CASE_SENSITIVE));
		$this->assertFalse($qr->hasOption(QrCode::CASE_INSENSITIVE));
		$this->assertFalse($qr->hasOption(QrCode::KANJI));
		$this->assertFalse($qr->hasOption(QrCode::ENCODE_8BIT));
	}



	public function testHasOption_SomeTrue()
	{
		$qr = new QrCode('nemam', 1, QrCode::ERR_CORR_L, 1, 1, QrCode::STRUCTURED | QrCode::CASE_SENSITIVE);
		$this->assertTrue($qr->hasOption(QrCode::STRUCTURED));
		$this->assertTrue($qr->hasOption(QrCode::CASE_SENSITIVE));
		$this->assertFalse($qr->hasOption(QrCode::CASE_INSENSITIVE));
		$this->assertFalse($qr->hasOption(QrCode::KANJI));
		$this->assertFalse($qr->hasOption(QrCode::ENCODE_8BIT));
	}



	public function testHasOption_Defaults()
	{
		$qr = new QrCode('nemam');
		$this->assertFalse($qr->hasOption(QrCode::STRUCTURED));
		$this->assertFalse($qr->hasOption(QrCode::STRUCTURED, 0));
		$this->assertFalse($qr->hasOption(QrCode::STRUCTURED, QrCode::KANJI));
		$this->assertTrue($qr->hasOption(QrCode::STRUCTURED, QrCode::STRUCTURED));
		$this->assertTrue($qr->hasOption(QrCode::STRUCTURED, QrCode::STRUCTURED | QrCode::STRUCTURED));
		$this->assertTrue($qr->hasOption(QrCode::STRUCTURED, QrCode::STRUCTURED | QrCode::KANJI));
		$this->assertTrue($qr->hasOption(QrCode::STRUCTURED, QrCode::STRUCTURED | QrCode::KANJI | QrCode::CASE_INSENSITIVE));
	}

}
