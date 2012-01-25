<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Curl;

use Kdyby;
use Kdyby\Curl;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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
