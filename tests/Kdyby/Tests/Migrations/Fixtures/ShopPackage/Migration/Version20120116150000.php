<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Migrations\Fixtures\ShopPackage\Migration;

use Doctrine\DBAL\Schema\Schema;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Version20120116150000 extends Kdyby\Migrations\AbstractMigration
{

	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function up(Schema $schema)
	{
		$this->addSql("INSERT INTO goods VALUES ('train')");
		$this->addSql("INSERT INTO goods VALUES ('car')");
	}

}
