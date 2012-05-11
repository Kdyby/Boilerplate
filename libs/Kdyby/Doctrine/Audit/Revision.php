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
 * Revision is returned from {@link AuditReader::getRevisions()}
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Revision extends Nette\Object
{
	/**
	 * @var int
	 */
    private $rev;

	/**
	 * @var \Datetime
	 */
    private $timestamp;

	/**
	 * @var string
	 */
    private $username;



	/**
	 * @param int $rev
	 * @param \Datetime $timestamp
	 * @param string $username
	 */
    public function __construct($rev, \Datetime $timestamp, $username)
    {
        $this->rev = $rev;
        $this->timestamp = $timestamp;
        $this->username = $username;
    }



	/**
	 * @return int
	 */
    public function getRev()
    {
        return $this->rev;
    }



	/**
	 * @return \Datetime
	 */
    public function getTimestamp()
    {
        return $this->timestamp;
    }



	/**
	 * @return string
	 */
    public function getUsername()
    {
        return $this->username;
    }

}
