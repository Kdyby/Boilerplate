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
class Version20120116140000 extends Kdyby\Migrations\AbstractMigration
{

	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function up(Schema $schema)
	{
		$table = $schema->createTable('articles');
		$table->addColumn('content', 'text');
		$table->addColumn('title', 'string');
	}



	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function down(Schema $schema)
	{
		$schema->dropTable('articles');
	}

}
