<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Tools;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class PresenterGenerator extendsNette\Object
{

	/** @var object */
	private $structure;

	/** @var path to module */
	private $cursor;

	/** @var string */
	private $appDir;



	/**
	 * @param string $appDir
	 */
	public function deploy($appDir)
	{
		$this->appDir = $appDir;

		foreach ($this->structure->modules as $module) {
			$this->renderModule($module);
		}
	}



	/**
	 * @param object $module
	 * @param array $parent
	 */
	private function renderModule($module, array $parent = NULL)
	{
		$parent = $parent ? $parent : array();

		$modulePath = array_merge($parent, (array)$module->name);

		$dirs = $this->prepareModule($module, $modulePath);

		if (isset($module->presenters)) {
			foreach ($module->presenters as $presenter) {
				$render = $this->renderPresenter($presenter, $modulePath);
				$this->writePresenter($render, $presenter, $dirs);
			}
		}

		if (isset($module->modules)) {
			foreach ($module->modules as $submodule) {
				$this->renderModule($submodule, $modulePath);
			}
		}
	}



	/**
	 * @param array $render
	 * @param object $presenter
	 * @param array $dirs
	 */
	private function writePresenter($render, $presenter, $dirs)
	{
		$file = $dirs['presenters'].'/'.$presenter->name.'Presenter.php';

		if (!file_exists($file)) { dump($file);
			file_put_contents($file, implode("\n", $render));
			@chmod($file, 0775);
		} // TODO: if file exists, use reflection and add content

		foreach ($this->renderViews($presenter) as $view => $render) {
			$file = $dirs['templates'].'/'.ucfirst($presenter->name).'.'.lcfirst($view).'.latte';

			if (!file_exists($file)) { dump($file);
				file_put_contents($file, $render);
				@chmod($file, 0775);
			}
		}
	}



	/**
	 * @param object $presenter
	 * @return array
	 */
	private function renderViews($presenter)
	{
		$views = array();

		if (isset($presenter->actions)) {
			foreach ($presenter->actions as $action) {
				$template = $this->getSettings($action, 'template');
				$views[$action->name] = $template['default'];
			}
		}

		return $views;
	}



	/**
	 * @param object $module
	 * @param array $modulePath
	 * @return array
	 */
	private function prepareModule($module, $modulePath)
	{
		$this->cursor = $modulePath;

		$dir = $this->appDir . '/' . implode('Module/', $modulePath)."Module";
		$dirs = array(
			$dir,
			'presenters' => $dir.'/presenters',
			'templates' => $dir.'/templates'
		);

		foreach ($dirs as $d) {
			@mkdir($d);
			@chmod($d, 0775);
		}

		$presenterNames = array_map(function($presenter) {
			return Nette\Utils\Strings::lower($presenter->name);
		}, $module->presenters);

		if (isset($module->presenters)) {
			if (!in_array('base', $presenterNames)) {
				$basePresenter = (object)array(
					'name' => 'Base',
					'abstract' => TRUE,
					'parent' => (count($this->cursor)>1 ? "../Base" : "Nette\\Application\\UI\\Presenter")
				);

				$render = $this->renderPresenter($basePresenter, $modulePath);
				$this->writePresenter($render, $basePresenter, $dirs);
			}

			foreach ($module->presenters as $presenter) {
				if (!isset($presenter->parent)) {
					$presenter->parent = 'Base';
				}
			}
		}

		return array(
			'presenters' => $dir.'/presenters',
			'templates' => $dir.'/templates'
		);
	}



	/**
	 * @param array $ns
	 * @return string
	 */
	private function nameModule($ns)
	{
		return ($ns ? implode("Module\\", $ns)."Module" : NULL);
	}



	/**
	 * @param object $presenter
	 * @param array $ns
	 * @return array
	 */
	private function renderPresenter($presenter, $ns)
	{
		$render['php'] = '<'."?php\n";

		$namespace = $this->nameModule($ns);
		$render['namespace'] = ($namespace ? "namespace ".$namespace.";" : NULL);

		if ($use = $this->getSettings($presenter, 'use')) {
			$render['use'] = "\nuse ".implode(";\nuse ", $use).";";
		}
		$render[] = "\n\n";

		if ($classDoc = $this->getSettings($presenter, 'annotations')) {
			$render['classDoc'] = "/**\n";
			foreach ($classDoc as $name => $definition) {
				$render['classDoc'] .= " * @".$name." ".$definition."\n";
			}
			$render['classDoc'] .= " */";
		}

		$abstract = (isset($presenter->abstract) && $presenter->abstract) ? "abstract " : NULL;
		$parent = $this->searchPresenter($presenter->parent);
		$render['start'] = $abstract."class ".$presenter->name."Presenter extends ".$parent. " \n{";
		
		if (isset($presenter->params)) {
			$this->renderProperties($render, $presenter->params);
			$render[] = "\n\n";
		}

		if (isset($presenter->actions)) {
			$this->renderActions($render, $presenter->actions);
		}

		$render['end'] = "}";

		return $render;
	}



	/**
	 * @param string $presenter
	 * @return string
	 */
	private function searchPresenter($presenter)
	{
		if (strpos($presenter, "\\") !== FALSE) {
			return $presenter;
		}

		if (strpos($presenter, '/') === FALSE) {
			return $presenter . 'Presenter';
		}

		$ns = $this->cursor;
		do {
			array_pop($ns);
			$presenter = substr($presenter, 3);
		} while (substr($presenter, 0, 3) == '../');

		$relativePath = Nette\Utils\Strings::split($presenter, '~:~');
		$presenter = array_pop($relativePath);

		$ns = array_merge((array)$ns, $relativePath);
		$module = ($ns ? "\\". implode("Module\\", $ns).'Module' : NULL);

		return $module . "\\" . $presenter . 'Presenter';
	}



	/**
	 * @param object $object
	 * @param string $var
	 * @return array
	 */
	private function getSettings($object, $var)
	{
		$settings = array();
		if (isset($this->structure->global) && isset($this->structure->global->{$var})) {
			$settings = array_merge($settings, (array)$this->structure->global->{$var});
		}
		if (isset($object->{$var})) {
			$settings = array_merge($settings, (array)$object->{$var});
		}

		return $settings;
	}



	/**
	 * @param array &$render
	 * @param array $params
	 */
	private function renderProperties(&$render, $params)
	{
		foreach ($params as $param) {
			$render['param'.$param->name] = "";
			if (isset($param->persistent) && $param->persistent) {
				$render['param'.$param->name] .= "	/** @persistent */\n";
			}

			$default = isset($param->default) ? $this->renderVariable($param->default) : NULL;
			$render['param'.$param->name] .= "	public \$".$param->name.($default ? ' = '.$default : NULL).";\n";
		}
	}



	/**
	 * @param mixed $variable
	 * @return string
	 */
	private function renderVariable($variable)
	{
		$type = isset($variable->type) ? $variable->type : 'string';
		return $this->convertLitteral($variable, $type);
	}



	/**
	 * @param array &$render
	 * @param array $actions
	 */
	private function renderActions(&$render, $actions)
	{
		foreach ($actions as $action) {
			$params = isset($action->params) ? $action->params : array();

			$render[] = "\n	/******************** ".$action->name." ********************/\n\n";
			$render['action'.$action->name] = $this->createMethod('action' . ucfirst($action->name), $params);
			$render[] = "\n\n";
			$render['render'.$action->name] = $this->createMethod('render' . ucfirst($action->name), $params)."\n";
		}
	}



	/**
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	private function createMethod($name, $params = array())
	{
		$defaults = array();
		foreach ($params as $param) {
			$default = NULL;
			if (isset($param->default)) {
				$type = isset($variable->type) ? $variable->type : NULL;
				$default = $this->convertLitteral($variable, $type);
			}
			$defaults[] = '$'. $param->name . ($default ? ' = '.$default : NULL);
		}

		$method = array('	public function '. $name. '('. implode(', ', $defaults).')');
		$method[] = '	{';
		$method[] = '';
		$method[] = '	}';

		return implode("\n", $method);
	}



	/**
	 * @param object $structure 
	 */
	public function setStructure($structure)
	{
		$this->structure = $structure;
	}



	/**
	 * @return object
	 */
	public function getStructure()
	{
		return $this->structure;
	}



	/**
	 * @param mixed $value
	 * @param string $type
	 * @return string
	 */
	protected function convertLitteral($value, $type)
	{
		if ($value === NULL) {
			return 'NULL';
		}

		switch ($type) {
			case 'string':
				return '"'.addslashes($value).'"';

			case 'int':
				return (int) $value;

			case 'float':
				return (float) $value;

			case 'bool':
				return ((bool) $value) ? "TRUE" : "FALSE";

			case 'array':
				return var_export((array)$value, TRUE);

			default:
				return $value;
		}
	}

}