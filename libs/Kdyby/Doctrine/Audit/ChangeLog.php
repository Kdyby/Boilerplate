<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Audit;

use Kdyby;
use Nette;



/**
 * This class represents complete log of all audited entities.
 * Here should be shortcuts for finding changes and listing them
 * - filter by date range
 * - filter by author
 * - filter by entity
 *
 * @todo: something-like-repository of Audit\Revision entity
 * @todo: return extended DAO or wrap it?
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ChangeLog extends Nette\Object
{


}
