<?php

namespace Kdyby\Control;

use Nette;
use Nette\String;
use Kdyby;


/**
 * Description of StepContainer
 *
 * + revizování každého kroku ->getRevisions() ->getLastRevision()
 * + plnění daty ->setValues(self::MERGED|self::STEPS, $data) ->addRevision('basics', $data)
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class StepContainer extends Lookout
{

	const STEPS = ':steps';
	const MERGED = ':merged';

	/** @persistent */
	public $token;

	/** @persistent */
	public $step;

	/** @var Nette\Web\SessionNamespace */
	private $data;

	/** @var array */
	private $steps = array();

	/** @var Kdyby\Control\Step */
	private $firstStep;

	/** @var string */
	public $stepsFinishedDataFormat = self::STEPS;



	public function __construct($parent, $name)
	{
		parent::__construct($parent, $name);

		if (!$parent instanceof Nette\ComponentContainer) {
			throw new InvalidStateException("StepContainer must be attached to component tree by constructor");
		}

		if (!$this->token) {
			$this->resetToken();
		}

		$session = $this->getSession($parent->reflection->name . '.' . $this->reflection->name);
		$this->data = $session[$this->token];
	}



	public function resetToken()
	{
		$this->token = substr(md5(uniqid()), 0, 6);
	}



	public function addStep($name, $title)
	{
		$step = new Step($this, $name, $title);

		if (!$this->steps) {
			$this->firstStep = $step;
		}

		return $this->steps[$name] = $step;
	}



	public function getSteps()
	{
		return $this->steps;
	}



	public function getNavigation()
	{
		$navigation = array();
		foreach ($this->getSteps() as $name => $step) {
			$link = new Link($this, 'this');
			$link->setParam('step', $name);

			$navigation[] = (object) array(
				'title' => $step->title,
				'link' => $link
			);
		}

		return $navigation;
	}



	public function setFirstStep(Step $step)
	{
		$this->firstStep = $step;
	}



	public function getFirstStep()
	{
		return $this->firstStep;
	}



	public function getData($format = self::STEPS)
	{
		switch ($format) {
			case self::STEPS:
				$data = array();
				foreach ($this->data as $step => $revisions) {
					$data[$step] = end($revisions);
				}
				break;

			case self::MERGED:
				$data = array();
				foreach ($this->data as $step => $revisions) {
					$data += end($revisions);
				}
				break;
		}

		return $data;
	}



	final public function attached($presenter)
	{
		$this->setupSteps();

		if ($this->step === NULL || !isset($this[$this->step]) || $this->getFirstStep() === NULL) {
			$this->step = reset($this->steps);
		}

		$this[$this->step]->attachEvents($this['form']);
		$this['form']->onSubmit = array(callback($this, 'fireEvents'));

		parent::attached($presenter);
	}



	abstract protected function setupSteps() { }



	/**
	 * @param string $name
	 * @return \Nette\Application\AppForm
	 */
	abstract protected function createComponentForm($name)
	{
		return $form = new Nette\Application\AppForm($this, $name);
	}



	public function createComponent($name)
	{
		if ($name == 'form') {
			$component = $this->createComponentForm($name);
			$this[$stepName] = $this->createComponentForm($stepName);
		}

		return parent::createComponent($name);
	}



	public function fireEvents($form)
	{
		
	}

}