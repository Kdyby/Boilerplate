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
 * @method string getPrefix()
 * @method string getSuffix()
 * @method string getFieldName()
 * @method string getTableName()
 * @method string getCurrentUsername()
 * @method setCurrentUsername(string $username)
 */
class AuditConfiguration extends Nette\Object
{
	/**
	 * @var string
	 */
    public $prefix;

	/**
	 * @var string
	 */
	public $suffix;

	/**
	 * @var string
	 */
	public $fieldName;

	/**
	 * @var string
	 */
	public $tableName;

	/**
	 * @var string
	 */
	public $currentUsername;



	/**
	 * @param $name
	 * @param $args
	 *
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return Nette\ObjectMixin::callProperty($this, $name, $args);
	}

}
