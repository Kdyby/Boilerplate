<?php

use Nette\Diagnostics\Debugger;
use Kdyby\Diagnostics\ConsoleDebugger;

// take care of autoloading
require_once __DIR__ . '/../autoload.php';


// additional php configurations
date_default_timezone_set("Europe/Prague");


// directories
$params = array(
	'wwwDir' => __DIR__,
	'appDir' => __DIR__,
	'logDir' => __DIR__ . '/log',
	'tempDir' => __DIR__ . '/temp',
);

// setup debugger
if (!is_writable($params['logDir'])) {
	throw new Kdyby\DirectoryNotWritableException("Logging directory '" . $params['logDir'] . "' is not writable.");
}
Debugger::enable(Debugger::DEVELOPMENT);
Debugger::$logDirectory = $params['logDir'];
Debugger::$maxLen = 4096;
// ConsoleDebugger::enable('google-chrome %s');


// create configurator
if (!is_writable($params['tempDir'])) {
	throw new Kdyby\DirectoryNotWritableException("Temp directory '" . $params['tempDir'] . "' is not writable.");
}
$configurator = new Kdyby\Tests\Configurator($params, new Kdyby\Package\DefaultPackages());
$container = $configurator->getContainer();


// start session on time
$container->session->start();


// delete exception reports from last run
foreach (Nette\Utils\Finder::findFiles('exception*.html', '*.log', 'dump*.html')->in($params['logDir']) as $file) {
	@unlink($file->getRealpath());
}


// don't you dare to "backup globals"!
unset($params, $configurator, $container, $file);
