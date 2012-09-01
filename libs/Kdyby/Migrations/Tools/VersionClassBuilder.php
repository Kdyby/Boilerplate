<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Migrations\Tools;

use Kdyby;
use Nette;
use Nette\Utils\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class VersionClassBuilder extends Nette\Object
{

	/** @var \Nette\Utils\PhpGenerator\ClassType */
	private $class;

	/** @var \Kdyby\Packages\Package */
	private $package;



	/**
	 * @param \Kdyby\Packages\Package $package
	 * @param string $name
	 */
	public function __construct(Kdyby\Packages\Package $package, $name = NULL)
	{
		$this->package = $package;
		$this->class = new Code\ClassType($name ?: 'Version' . date('YmdHis'));
		$this->class->addExtend('Kdyby\Migrations\AbstractMigration');
		$this->class->addDocument("@todo: write description of migration");

		$up = $this->class->addMethod('up');
		$up->addParameter('schema')->setTypeHint('Schema');
		$up->addBody("// this method was auto-generated, please modify it to your needs\n");
	}



	/**
	 * @param string $sql
	 * @param array $params
	 */
	public function addUpSql($sql, array $params = array())
	{
		/** @var \Nette\Utils\PhpGenerator\Method $up */
		$up = $this->class->methods['up'];
		$up->addBody('$this->addSql(?,?)', array($sql, $params));
	}



	/**
	 * @param string $sql
	 * @param array $params
	 */
	public function addDownSql($sql, array $params = array())
	{
		if (!isset($this->class->methods['down'])) {
			$down = $this->class->addMethod('down');
			$down->addParameter('schema')->setTypeHint('Schema');
			$down->addBody("// this method was auto-generated, please modify it to your needs\n");
		}

		/** @var \Nette\Utils\PhpGenerator\Method $down */
		$down = $this->class->methods['down'];
		$down->addBody('$this->addSql(?,?)', array($sql, $params));
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

		return '<?php' . "\n\n" . $s . "\n\n\n" . (string)$this->class;
	}

}
