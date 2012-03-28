<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Templates;

use Kdyby;
use Kdyby\Templates\EditableTemplates;
use Kdyby\Templates\TemplateSource;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EditableTemplatesTest extends Kdyby\Tests\OrmTestCase
{

	/**
	 * @var \Kdyby\Caching\LatteStorage
	 */
	private $storage;

	/**
	 * @var \Kdyby\Doctrine\Dao
	 */
	private $dao;

	/**
	 * @var \Kdyby\Templates\EditableTemplates
	 */
	private $templates;




	public function setUp()
	{
		$this->createOrmSandbox(array('Kdyby\Templates\TemplateSource'));

		$cacheDir = $this->getContext()->expand('%tempDir%/cache');
		if ($nsDirs = glob($cacheDir . '/*' . EditableTemplates::CACHE_NS . '*')) {
			Kdyby\Tools\Filesystem::rmDir(reset($nsDirs));
		}

		$this->storage = new Kdyby\Caching\LatteStorage($cacheDir);
		$this->templates = new EditableTemplates($this->getDoctrine(), $this->storage);

		$this->dao = $this->getDao('Kdyby\Templates\TemplateSource');
	}



	public function testSavedTemplateHasAFile()
	{
		$template = new TemplateSource;
		$template->setSource('{$name}');

		$this->templates->save($template);
		$this->assertNotNull($id = $template->getId());
		$this->getEntityManager()->flush();

		$template = $this->dao->getReference($id);
		$file = $this->templates->getTemplateFile($template);

		$this->assertEquals($template->getSource(), static::readTemplate($file));
	}



	public function testFileWillBeRestoredWhenDeleted()
	{
		$template = new TemplateSource;
		$template->setSource('{$name}');

		$this->templates->save($template);
		$file = $this->templates->getTemplateFile($template);
		$this->assertFileExists($file);

		Kdyby\Tools\Filesystem::rm($file);
		$this->assertFileNotExists($file);

		$file = $this->templates->getTemplateFile($template);
		$this->assertFileExists($file);
	}



	/**
	 * @group one
	 */
	public function testTemplateCanBeExtended()
	{
		$layout = new TemplateSource;
		$layout->setSource('<div>{include #content}</div>');

		$template = new TemplateSource;
		$template->setSource('{block #content}{$name}{/block}');
		$template->setExtends($layout);

		$this->templates->save($template);
		$this->assertNotNull($id = $template->getId());
		$this->assertNotNull($layout->getId());
		$this->getEntityManager()->flush();

		$template = $this->dao->getReference($id);
		$file = $this->templates->getTemplateFile($template);

		$this->assertStringMatchesFormat(
			"{extends %s}\n". $template->getSource(),
			static::readTemplate($file)
		);
	}



	/**
	 * @param string $file
	 * @return string
	 */
	private static function readTemplate($file)
	{
		ob_start();
		Nette\Utils\LimitedScope::evaluate(file_get_contents($file));
		return ob_get_clean();
	}

}
