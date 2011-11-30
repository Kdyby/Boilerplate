<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Package;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DirectoryPackagesTest extends \Kdyby\Tests\TestCase
{

    public function testGettingPackages()
    {
        $finder = new \Kdyby\Package\DirectoryPackages(__DIR__ . '/../Fixtures', 'Kdyby\\Tests\\Fixtures');
        $this->assertEquals(array(
                 'Kdyby\\Tests\\Fixtures\\BarPackage\\BarPackage',
                 'Kdyby\\Tests\\Fixtures\\FooPackage\\FooPackage',
            ), $finder->getPackages());
    }

}
