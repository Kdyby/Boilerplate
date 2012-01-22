<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations;

use Kdyby;
use Nette;
use Nette\Utils\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class VersionClassBuilder extends Nette\Object
{

	/** @var \Nette\Utils\PhpGenerator\ClassType */
	private $class;

	/** @var \Kdyby\Packages\Package */
	private $package;



	/**
	 * @param \Kdyby\Packages\Package $package
	 */
	public function __construct(Kdyby\Packages\Package $package)
	{
		$this->package = $package;
		$this->class = new Code\ClassType('Version' . date('YmdHis'));
		$this->class->addExtend('Kdyby\Migrations\AbstractMigration');

		$up = $this->class->addMethod('up');
		$up->addParameter('schema')->setTypeHint('Schema');
		$up->addBody('// this method was auto-generated, please modify it to your needs');
	}



	/**
	 * @param string $sql
	 * @param array $params
	 * @param array $types
	 */
	public function addUpSql($sql, array $params = array(), array $types = array())
	{
		$this->class->methods['up']
			->addBody('$this->addSql(?,?,?)', array($sql, $params, $types));
	}



	/**
	 * @param string $sql
	 * @param array $params
	 * @param array $types
	 */
	public function addDownSql($sql, array $params = array(), array $types = array())
	{
		if (!isset($this->class->methods['down'])) {
			$down = $this->class->addMethod('down');
			$down->addParameter('schema')->setTypeHint('Schema');
			$down->addBody('// this method was auto-generated, please modify it to your needs');
		}

		$this->class->methods['down']
			->addBody('$this->addSql(?,?,?)', array($sql, $params, $types));
	}



	/**
	 * @return string
	 */
	public function build()
	{
		$s = 'namespace ' . $this->package->getNamespace() . '\Migration;' . "\n\n";
		$s .= 'use Doctrine\DBAL\Schema\Schema;' . "\n";
		$s .= 'use Kdyby;' . "\n";
		$s .= 'use Nette;' . "\n";

		return $s . "\n\n\n" . (string)$this->class;
	}

}
