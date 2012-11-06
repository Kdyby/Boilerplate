<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Nette\Diagnostics\Debugger;
use Kdyby\Reflection\FunctionCode;
use Kdyby\Reflection\NamespaceUses;
use Nette\Reflection\GlobalFunction;
use Nette;
use Nette\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ClosureExtractor extends Nette\Object
{

	/**
	 * @var GlobalFunction
	 */
	private $closure;



	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure)
	{
		$this->closure = new GlobalFunction($closure);
	}



	/**
	 * @param \ReflectionClass $class
	 * @return string
	 */
	public function buildScript(\ReflectionClass $class = NULL)
	{
		$uses = new NamespaceUses($class);
		$codeParser = new FunctionCode($this->closure);

		$code = '<?php' . "\n\n";

		if ($class) {
			$code .= "namespace " . $class->getNamespaceName() . ";\n\n";
			$code .= 'use ' . implode(";\nuse ", $uses->parse()) . ";\n";
		}

		$code .= "\n";

		// bootstrap
		if (!empty($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
			$code .= Code\Helpers::formatArgs('require_once ?;', array($GLOBALS['__PHPUNIT_BOOTSTRAP'])) . "\n";
		}

		// debugging
		$code .= __CLASS__ . '::errorHandlers();' . "\n\n\n";

		// script
		$code .= Code\Helpers::formatArgs('extract(?);', array($this->closure->getStaticVariables())) . "\n";
		$code .= $codeParser->parse() . "\n\n\n";

		// close session
		$code .= 'Kdyby\Tests\Configurator::getTestsContainer()->session->close();' . "\n\n";

		return $code;
	}



	public static function errorHandlers()
	{
		$container = Kdyby\Tests\Configurator::getTestsContainer();
		$response = $container->httpResponse;
		$response->setHeader('Content-Type', 'text/plain');

		Debugger::$onFatalError[] = function (\Exception $e) use ($response) {
			$response->setHeader('X-Nette-Error-Type', get_class($e));
			$response->setHeader('X-Nette-Error-Message', $e->getMessage());
			exit(Process::CODE_ERROR);
		};
	}

}
