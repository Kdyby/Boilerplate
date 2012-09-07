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
class Version20120116160000 extends Kdyby\Migrations\AbstractMigration
{

	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function up(Schema $schema)
	{
		$this->addSql("UPDATE articles SET content='cars are way more cool!' WHERE title='cars'");
	}



	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function down(Schema $schema)
	{
		$this->addSql("UPDATE articles SET content='cars are fun' WHERE title='cars'");
	}

}
