<?php

namespace Gridito;

/**
 * Action button
 *
 * @author Jan Marek
 * @license MIT
 */
class Button extends BaseButton
{
	/** @var bool */
	private $ajax = false;

	/** @var string|callback|null */
	private $confirmationQuestion = null;
	
	

	/**
	 * Is ajax?
	 * @return bool
	 */
	public function isAjax()
	{
		return $this->ajax;
	}



	/**
	 * Set ajax mode
	 * @param bool ajax
	 * @return Button
	 */
	public function setAjax($ajax)
	{
		$this->ajax = (bool) $ajax;
		return $this;
	}



	/**
	 * Get confirmation question
	 * @param mixed row
	 * @return string|callback|null
	 */
	public function getConfirmationQuestion($row)
	{
		if (is_callable($this->confirmationQuestion)) {
			return call_user_func($this->confirmationQuestion, $row);
		} else {
			return $this->confirmationQuestion;
		}
	}



	/**
	 * Set confirmation question
	 * @param string|callback|null confirmation question
	 * @return Button
	 */
	public function setConfirmationQuestion($confirmationQuestion)
	{
		$this->confirmationQuestion = $confirmationQuestion;
		return $this;
	}

	

	/**
	 * Handle click signal
	 * @param string security token
	 * @param mixed primary key
	 */
	public function handleClick($token, $uniqueId = null)
	{
		parent::handleClick($token, $uniqueId);

		if ($this->getPresenter()->isAjax()) {
			$this->getGrid()->invalidateControl();
		} else {
			$this->getGrid()->redirect("this");
		}
	}



	/**
	 * Create button element
	 * @param mixed row
	 * @return Nette\Web\Html
	 */
	protected function createButton($row = null)
	{
		$el = parent::createButton($row);
		$el->class[] = $this->isAjax() ? $this->getGrid()->getAjaxClass() : null;
		$el->data("gridito-question", $this->getConfirmationQuestion($row));
		
		return $el;
	}

}