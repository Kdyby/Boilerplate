<?php

require_once LIBS_DIR . '/Symfony/Component/HttpFoundation/UniversalClassLoader.php';

use Symfony\Component\HttpFoundation\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony' => LIBS_DIR,
));
$loader->register();
