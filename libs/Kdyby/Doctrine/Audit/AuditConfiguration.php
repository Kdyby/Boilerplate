<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit;

use Nette;



/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method string getTablePrefix()
 * @method setTablePrefix(string $prefix)
 * @method string getTableSuffix()
 * @method setTableSuffix(string $suffix)
 * @method string getRevisionFieldName()
 * @method setRevisionFieldName(string $revisionFieldName)
 * @method string getRevisionTypeFieldName()
 * @method setRevisionTypeFieldName(string $revisionTypeFieldName)
 * @method string getRevisionTableName()
 * @method setRevisionTableName(string $revisionTableName)
 * @method string getCurrentUsername()
 * @method setCurrentUsername(string $username)
 * @method string getRevisionIdFieldType()
 * @method setRevisionIdFieldType(string $revisionIdFieldType)
 */
class AuditConfiguration extends Nette\Object
{
	/**
	 * @var string
	 */
    public $prefix = '';

	/**
	 * @var string
	 */
	public $suffix = '_audit';

	/**
	 * @var string
	 */
	public $revisionFieldName = 'rev';

	/**
	 * @var string
	 */
	public $revisionTypeFieldName = 'revtype';

	/**
	 * @var string
	 */
	public $revisionTableName = 'revisions';

	/**
	 * @var string
	 */
	public $currentUsername = '';

	/**
	 * @var string
	 */
	public $revisionIdFieldType = 'integer';



	/**
	 * @param $name
	 * @param $args
	 *
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return \Nette\ObjectMixin::callProperty($this, $name, $args);
	}

}
