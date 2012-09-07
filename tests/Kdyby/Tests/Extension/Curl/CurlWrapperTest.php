<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\Curl;

use Kdyby;
use Kdyby\Extension\Curl;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CurlWrapperTest extends Kdyby\Tests\TestCase
{
	const TEST_PATH = 'http://www.kdyby.org/curl-test';

	/** @var string */
	private $tempFile;



	public function setUp()
	{
		$this->skipIfNoInternet();
	}



	protected function tearDown()
	{
		if ($this->tempFile && file_exists($this->tempFile)) {
			@unlink($this->tempFile);
		}
	}



	public function testGet()
	{
		$curl = new Curl\CurlWrapper(static::TEST_PATH . '/get.php');

		$this->assertTrue($curl->execute());
		$this->assertEquals($this->dumpVar(array()), $curl->response);
	}



	public function testPost()
	{
		$curl = new Curl\CurlWrapper(static::TEST_PATH . '/post.php', Curl\Request::POST);
		$curl->setPost($post = array('hi' => 'hello'));

		$this->assertTrue($curl->execute());
		$this->assertEquals($this->dumpVar($post) . $this->dumpVar(array()), $curl->response);
	}



	public function testPostFiles()
	{
		$curl = new Curl\CurlWrapper(static::TEST_PATH . '/post.php', Curl\Request::POST);
		$curl->setPost($post = array('hi' => 'hello'), $files = array('txt' => $this->tempFile()));
		$this->assertTrue($curl->execute());
		$this->assertStringMatchesFormat($this->dumpVar($post) . $this->dumpPostFiles($files), $curl->response);
	}



	public function testGet_Cookies()
	{
		$curl = new Curl\CurlWrapper(static::TEST_PATH . '/cookies.php');
		$curl->setOption('header', TRUE);
		$this->assertTrue($curl->execute());

		$headers = Curl\Response::stripHeaders($curl);
		$this->assertEquals(Curl\HttpCookies::from(array(
			'kdyby' => 'is awesome',
			'nette' => 'is awesome',
			'array' => array(
				'one' => 'Lister',
				'two' => 'Rimmer'
			),
		), FALSE), $headers['Set-Cookie']);
	}



	/**
	 * @param mixed $variable
	 * @return string
	 */
	private function dumpVar($variable)
	{
		ob_start();
		print_r($variable);
		return ob_get_clean();
	}



	/**
	 * @return string
	 */
	private function tempFile()
	{
		$this->tempFile = $this->getContext()->expand('%tempDir%') . '/curl-test.txt';
		file_put_contents($this->tempFile, 'ping');
		@chmod($this->tempFile, 0755);
		return $this->tempFile;
	}



	/**
	 * @param array $files
	 * @return string
	 */
	private function dumpPostFiles($files)
	{
		array_walk_recursive($files, function (&$input, $key) {
			$input = array(
				'name' => basename($input),
				'type' => '%s',
				'tmp_name' => '%s',
				'error' => '0',
				'size' => filesize($input),
			);
		});

		return $this->dumpVar($files);
	}

}
