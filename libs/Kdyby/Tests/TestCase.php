<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests;

use Kdyby;
use Nette;
use Nette\Application\UI;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var \SystemContainer|\Nette\DI\Container
	 */
	private $context;

	/**
	 * @var Tools\TempClassGenerator
	 */
	private $tempClassGenerator;



	/**
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		$this->context = Kdyby\Tests\Configurator::getTestsContainer();
		$this->tempClassGenerator = new Tools\TempClassGenerator($this->getContext()->expand('%tempDir%'));

		parent::__construct($name, $data, $dataName);
	}



	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	public function getContext()
	{
		return $this->context;
	}



	/**
	 * Skip test if domain kdyby.org is unreachable
	 */
	protected function skipIfNoInternet()
	{
		if ('pong' !== @file_get_contents('http://www.kdyby.org/ping')) {
			$this->markTestSkipped('No internet connection');
		}
	}



	/**
	 * @param string $neonFile
	 * @param array $extensions
	 *
	 * @return \Nette\DI\Container|\SystemContainer
	 */
	protected function createContainer($neonFile = NULL, array $extensions = array())
	{
		// configurator
		$config = new Nette\Config\Configurator();
		$config->setDebugMode(TRUE);
		$config->onCompile[] = function ($config, Nette\Config\Compiler $compiler) use ($extensions) {
			/** @var \Nette\Config\CompilerExtension $ext */
			foreach ($extensions as $name => $ext) {
				$compiler->addExtension($name, $ext);
			}
		};

		// unique container name & dir
		$id = uniqid();
		$config->addParameters(array('container' => array('class' => 'SystemContainer' . $id)));
		$tempDir = $this->getContext()->expand('%tempDir%/cache/' . $id);
		@mkdir($tempDir, 0777);
		$config->setTempDirectory($tempDir);

		// configuration
		$testsConfig = $this->getContext()->expand('%appDir%/config.neon');
		$config->addConfig($testsConfig, $config::NONE);
		if ($neonFile !== NULL) {
			$config->addConfig($neonFile, $config::NONE);
		}

		// create container
		return $config->createContainer();
	}



	/********************* Asserts *********************/


	/**
	 * @param array|\Nette\Callback|\Closure $callback
	 * @param Nette\Object $object
	 * @param string $eventName
	 */
	public function assertEventHasCallback($callback, $object, $eventName)
	{
		$this->assertCallable($callback);

		$constraint = new Constraint\EventHasCallbackConstraint($object, $eventName);
		self::assertThat($callback, $constraint, NULL);
	}



	/**
	 * @param array $collection
	 * @param array $lists
	 * @param array $mappers
	 * @param boolean $allowOnlyMentioned
	 * @param boolean $allowDuplications
	 */
	public function assertContainsCombinations($collection, array $lists, array $mappers, $allowOnlyMentioned = TRUE, $allowDuplications = FALSE)
	{
		$constraint = new Constraint\ContainsCombinationConstraint($lists, $mappers);
		$constraint->allowDuplications = $allowDuplications;
		$constraint->allowOnlyMentioned = $allowOnlyMentioned;
		self::assertThat($collection, $constraint, NULL);
	}



	/**
	 * Given callback must return TRUE, when the condition is met, FALSE otherwise
	 *
	 * @param array $collection
	 * @param callable $callback
	 */
	public function assertItemsMatchesCondition($collection, $callback)
	{
		$callback = callback($callback);
		$i = 0;
		foreach ($collection as $item) {
			$this->assertTrue($callback($item), "Item #" . $i . " matches the conditions from callback.");
			$i++;
		}
	}



	/**
	 * @param callable $callback
	 * @param string $message
	 */
	public function assertCallable($callback, $message = NULL)
	{
		$constraint = new Constraint\IsCallableConstraint();
		self::assertThat($callback, $constraint, $message);
	}


	/********************* Mocking *********************/


	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|\Closure
	 */
	public function getCallbackMock()
	{
		return $this->getMockBuilder('Kdyby\Tests\Tools\Callback')
			->disableOriginalConstructor()
			->getMock();
	}



	/**
	 * @param \Nette\ComponentModel\IComponent $component
	 * @param array $methods
	 * @param string $name
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject|\Kdyby\Application\UI\Presenter
	 */
	public function attachToPresenter(Nette\ComponentModel\IComponent $component, $methods = array(), $name = 'component')
	{
		/** @var \PHPUnit_Framework_MockObject_MockObject|\Kdyby\Application\UI\Presenter $presenter */
		$presenter = $this->getMock('Kdyby\Application\UI\Presenter', (array)$methods, array());
		$this->getContext()->callMethod(array($presenter, 'injectPrimary'));
		$component->setParent($presenter, $name);
		return $presenter;
	}


	/********************* DataProvider *********************/


	/**
	 * @param string $inputMask
	 * @param string $outputMask
	 *
	 * @return array[]
	 */
	protected function findInputOutput($inputMask, $outputMask)
	{
		$finder = new Tools\FilesPairsFinder($this);
		return $finder->find($inputMask, $outputMask);
	}


	/********************* Nette Forms *********************/


	/**
	 * @param \Nette\Application\UI\Form $form
	 * @param array $values
	 */
	public function submitForm(UI\Form $form, array $values = array())
	{
		$get = $form->getMethod() !== UI\Form::POST ? $values : array();
		$post = $form->getMethod() === UI\Form::POST ? $values : array();
		list($post, $files) = $this->separateFilesFromPost($post);

		$presenter = new Tools\UIFormTestingPresenter($form);
		$this->getContext()->callMethod(array($presenter, 'injectPrimary'));
		return $presenter->run(new Nette\Application\Request(
			'presenter',
			strtoupper($form->getMethod()),
			array('do' => 'form-submit', 'action' => 'default') + $get,
			$post,
			$files
		));
	}



	/**
	 * @param array $post
	 * @param array $files
	 *
	 * @return array
	 */
	private function separateFilesFromPost(array $post, array $files = array())
	{
		foreach ($post as $key => $value) {
			if (is_array($value)) {
				list($pPost, $pFiles) = $this->separateFilesFromPost($value);
				unset($post[$key]);

				if ($pPost) {
					$post[$key] = $pPost;
				}
				if ($pFiles) {
					$files[$key] = $pFiles;
				}
			}

			if ($value instanceof Nette\Http\FileUpload) {
				$files[$key] = $value;
				unset($post[$key]);
			}
		}

		return array($post, $files);
	}


	/********************* TempClassGenerator *********************/


	/**
	 * @return Tools\TempClassGenerator
	 */
	private function getTempClassGenerator()
	{
		return $this->tempClassGenerator;
	}



	/**
	 * @param string $class
	 * @return string
	 */
	protected function touchTempClass($class = NULL)
	{
		return $this->getTempClassGenerator()->generate($class);
	}



	/**
	 * @param string $class
	 * @return string
	 */
	protected function resolveTempClassFilename($class)
	{
		return $this->getTempClassGenerator()->resolveFilename($class);
	}


	/********************* Exceptions handling *********************/


	/**
	 * This method is called when a test method did not execute successfully.
	 *
	 * @param \Exception $e
	 */
	protected function onNotSuccessfulTest(\Exception $e)
	{
		if (!$e instanceof \PHPUnit_Framework_AssertionFailedError) {
			Nette\Diagnostics\Debugger::log($e);
			Kdyby\Diagnostics\ConsoleDebugger::_exceptionHandler($e);
		}

		parent::onNotSuccessfulTest($e);
	}


	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return \Nette\Reflection\ClassType
	 */
	public static function getReflection()
	{
		return new Nette\Reflection\ClassType(get_called_class());
	}



	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		ObjectMixin::set($this, $name, $value);
	}



	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}



	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}
