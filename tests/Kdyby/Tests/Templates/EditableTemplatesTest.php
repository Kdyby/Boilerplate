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

		$this->storage = new Kdyby\Caching\LatteStorage($this->getContext()->expand('%tempDir%/cache'));
		$this->templates = new EditableTemplates($this->getDoctrine(), $this->storage);

		$this->dao = $this->getDao('Kdyby\Templates\TemplateSource');
	}



	public function test()
	{
		$template = new TemplateSource;
		$template->setSource('{$name}');

		$this->templates->save($template);
		$this->assertNotNull($id = $template->getId());
		$this->getEntityManager()->flush();

		$template = $this->dao->getReference($id);
		$file = $this->templates->getTemplateFile($template);

		ob_start();
		Nette\Utils\LimitedScope::evaluate(file_get_contents($file));
		$source = ob_get_clean();

		$this->assertEquals($template->getSource(), $source);
	}

}
