<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace KdybyTests\Application;

use Kdyby;
use Kdyby\DependencyInjection\DefaultServiceFactories;
use Nette;



class PresenterTest extends Kdyby\Testing\TestCase
{
	/** @var PresenterMock */
	private $presenter;



	public function setUp()
	{
		$serviceContainer = new Kdyby\DependencyInjection\ServiceContainer();
		$serviceContainer->addService('Kdyby\Registry\NamespacePrefixes', DefaultServiceFactories::createRegistryNamespacePrefixes());
		$serviceContainer->addAlias('namespacePrefixes', 'Kdyby\Registry\NamespacePrefixes');
		$serviceContainer->addService('Kdyby\Registry\TemplateDirs', DefaultServiceFactories::createRegistryTemplateDirs());
		$serviceContainer->addAlias('templateDirs', 'Kdyby\Registry\TemplateDirs');

		$this->presenter = new PresenterMock;
		$this->presenter->setServiceContainer($serviceContainer);
	}



	/**
	 * @test
	 */
	public function formatLayoutTemplatesFiles()
	{
		$appDir = $this->presenter->getServiceContainer()->templateDirs['app'];
		$frameworkDir = $this->presenter->getServiceContainer()->templateDirs['framework'];

		$this->assertEquals(array(
			$appDir . "/templates/Foo/@layout.latte",
			$appDir . "/templates/Foo.@layout.latte",
			$appDir . "/templates/@layout.latte",
			$frameworkDir . "/templates/Foo/@layout.latte",
			$frameworkDir . "/templates/Foo.@layout.latte",
			$frameworkDir . "/templates/@layout.latte",
		), $this->presenter->formatLayoutTemplateFiles('Foo', 'layout'),
		"->formatLayoutTemplateFiles('Foo', 'bar')");

		$this->assertEquals(array(
			$appDir . "/FooModule/templates/Bar/@layout.latte",
			$appDir . "/FooModule/templates/Bar.@layout.latte",
			$appDir . "/FooModule/templates/@layout.latte",
			$appDir . "/templates/@layout.latte",
			$frameworkDir . "/FooModule/templates/Bar/@layout.latte",
			$frameworkDir . "/FooModule/templates/Bar.@layout.latte",
			$frameworkDir . "/FooModule/templates/@layout.latte",
			$frameworkDir . "/templates/@layout.latte",
		), $this->presenter->formatLayoutTemplateFiles('Foo:Bar', 'layout'),
		"->formatLayoutTemplateFiles('Foo:Bar', 'layout')");
	}



	/**
	 * @test
	 */
	public function formatTemplatesFiles()
	{
		$appDir = $this->presenter->getServiceContainer()->templateDirs['app'];
		$frameworkDir = $this->presenter->getServiceContainer()->templateDirs['framework'];

		$mapper = function ($path) {
			return @realpath($path);
		};

		$this->assertEquals(array(
			$appDir . "/templates/Foo/bar.latte",
			$appDir . "/templates/Foo.bar.latte",
			$frameworkDir . "/templates/Foo/bar.latte",
			$frameworkDir . "/templates/Foo.bar.latte",
		), $this->presenter->formatTemplateFiles('Foo', 'bar'),
		"->formatTemplateFiles('Foo', 'bar')");

		$this->assertEquals(array(
			$appDir . "/FooModule/templates/Bar/baz.latte",
			$appDir . "/FooModule/templates/Bar.baz.latte",
			$frameworkDir . "/FooModule/templates/Bar/baz.latte",
			$frameworkDir . "/FooModule/templates/Bar.baz.latte",
		), $this->presenter->formatTemplateFiles('Foo:Bar', 'baz'),
		"->formatTemplateFiles('Foo:Bar', 'baz')");
	}

}
