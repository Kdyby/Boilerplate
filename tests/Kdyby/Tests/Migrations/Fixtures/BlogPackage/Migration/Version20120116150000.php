<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Migrations\Fixtures\BlogPackage\Migration;

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
		$this->addSql("INSERT INTO articles VALUES ('trains are cool', 'trains')");
		$this->addSql("INSERT INTO articles VALUES ('car are fun', 'cars')");
	}



	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function down(Schema $schema)
	{
		$this->addSql("DELETE FROM articles WHERE title='trains'");
		$this->addSql("DELETE FROM articles WHERE title='cars'");
	}

}
