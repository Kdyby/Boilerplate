<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Types;

use Doctrine;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Enum extends Type
{

	/**
	 * @param array $fieldDeclaration
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 *
	 * @throws \Kdyby\InvalidStateException
	 * @return mixed
	 */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        throw new Kdyby\InvalidStateException(
			"Please, use the 'columnDefinition' property of @Column() annotation."
		);
    }



	/**
	 * @return string
	 */
    public function getName()
    {
        return 'enum';
    }

}
