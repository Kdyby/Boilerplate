<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Packages;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DirectoryPackagesTest extends Kdyby\Tests\TestCase
{

    public function testGettingPackages()
    {
		$dir = realpath(__DIR__ . '/../Package/Fixtures');
        $finder = new Kdyby\Packages\DirectoryPackages($dir, 'Kdyby\Tests\Package\Fixtures');
        $this->assertEquals(array(
                 'Kdyby\Tests\Package\Fixtures\BarPackage\BarPackage',
                 'Kdyby\Tests\Package\Fixtures\FooPackage\FooPackage',
            ), $finder->getPackages());
    }

}
