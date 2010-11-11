<?php

namespace Kdyby\Database;

use Nette;
use Kdyby;



/**
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
interface IEntityMapper
{

	public function save(IEntity $entity);

	public function query(Query $query);

}
