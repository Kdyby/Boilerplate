<?php

namespace Kdyby\AdminModule;

use Kdyby;
use Kdyby\Components\Grinder\Actions\ButtonAction;
use Kdyby\Components\Grinder\Actions\LinkAction;
use Nette;
use Nette\Utils\Strings;
use Doctrine;



class DashboardPresenter extends BasePresenter
{

	public function actionDefault()
	{
	}



	protected function createComponentGrinder($name)
	{
		$em = $this->serviceContainer->entityManager;
		$entityName = 'Kdyby\Application\Presentation\Sitemap';
		$model = new Kdyby\Components\Grinder\Models\SimpleDoctrineModel($em, $entityName);

		$grid = new Kdyby\Components\Grinder\Grid($model);
		$grid->setUpProtection($this->getSession());
		$grid->setRenderer(new Kdyby\Components\Grinder\Renderers\TableRenderer);


		$grid->addCheckColumn('select');
		$grid->addColumn('name', 'Jméno');
		$grid->addColumn('sequence', 'identifikátor');

		$column = $grid->addActionsColumn('detail', NULL, array(
			'caption' => 'Detaily',
			'handler' => callback($this, 'DetailsClicked')
		));
		$column->addAction(new LinkAction, 'other')
			->setCaption('Jiné')
			->setHandler(callback($this, 'JineClicked'));

		$grid->addColumn('destination', 'Cíl');

		$grid->addAction('edit', 'Upravit')
			->setLink($this->lazyLink('edit!'), array('sitemapId' => 'id')); // přidá akci na konec gridu

		$grid->addAction('delete', 'Smazat', array(
			'handler' => callback($this, 'DeleteClicked')
		)); // přidá akci na konec gridu


		$grid->addToolbarAction('send', 'Vypsat')
			->onSubmit[] = callback($this, 'VypsatSubmitted');

		return $grid;
	}



	public function DetailsClicked(LinkAction $action, $id)
	{
		$action->getGrid()->flashMessage('detaily ' . $id);
	}



	public function JineClicked(LinkAction $action, $id)
	{
		$action->getGrid()->flashMessage('jine ' . $id);
	}



	public function handleEdit($sitemapId)
	{
		$this->flashMessage('bar ' . $sitemapId);
		$this->redirect('this');
	}



	public function DeleteClicked(LinkAction $action, $id)
	{
		$action->getGrid()->flashMessage('foo ' . $id);
	}



	/**
	 * @param ButtonAction $grid
	 */
	public function VypsatSubmitted(ButtonAction $action)
	{
		$s = $action->getGrid()->getColumn('select');
		dump(count($s->getChecked()), $s->getValues());
	}



	protected function afterRender()
	{
		parent::afterRender();

		Nette\Diagnostics\Debugger::$maxDepth = 6;
		Nette\Diagnostics\Debugger::barDump($_SESSION, 'Session');
	}

}