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
 */
class AuditException extends \Exception
{

	/**
	 * @param $className
	 * @return AuditException
	 */
    static public function notAudited($className)
    {
        return new self("Class '" . $className . "' is not audited.");
    }



	/**
	 * @param $className
	 * @param $id
	 * @param $revision
	 * @return AuditException
	 */
    static public function noRevisionFound($className, $id, $revision)
    {
        return new self("No revision of class '" . $className . "' (".implode(", ", $id).") was found ".
            "at revision " . $revision . " or before. The entity did not exist at the specified revision yet.");
    }



	/**
	 * @param $rev
	 * @return AuditException
	 */
    static public function invalidRevision($rev)
    {
        return new self("No revision '".$rev."' exists.");
    }

}
