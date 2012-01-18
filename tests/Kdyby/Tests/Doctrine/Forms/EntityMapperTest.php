<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Doctrine\Forms;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Kdyby\Doctrine\Forms\CollectionContainer;
use Kdyby\Doctrine\Forms\EntityMapper;
use Kdyby\Doctrine\Forms\EntityContainer;
use Kdyby\Doctrine\Forms\Form;
use Nette;
use Nette\Forms\Controls;
use Nette\Forms\IControl;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EntityMapperTest extends Kdyby\Tests\OrmTestCase
{

	/** @var \Kdyby\Doctrine\Forms\EntityMapper */
	private $mapper;



	public function setUp()
	{
		$this->createOrmSandbox(array(
			__NAMESPACE__ . '\Fixtures\RootEntity',
			__NAMESPACE__ . '\Fixtures\RelatedEntity',
		));

		$this->mapper = new EntityMapper($this->getDoctrine());
	}



	public function testAssigningEntity()
	{
		$entity = new Fixtures\RootEntity("Chuck Norris");
		$component = new Nette\Forms\Container;

		$this->mapper->assign($entity, $component);
		$this->assertSame($entity, current($this->mapper->getEntities()));
		$this->assertSame($component, $this->mapper->getComponent($entity));
	}



	public function testSettingControlAlias()
	{
		$name = new Controls\TextInput('Name');
		$name->setParent(NULL, 'name');

		$surname = new Controls\TextInput('Surname');
		$surname->setParent(NULL, 'surname');

		$this->mapper->setControlAlias($name, 'title');

		$this->assertEquals('title', $this->mapper->getControlField($name));
		$this->assertEquals('surname', $this->mapper->getControlField($surname));
	}



	/**
	 * @return array
	 */
	public function dataItemControls()
	{
		return array(
			array(new Controls\SelectBox),
			array(new Kdyby\Forms\Controls\CheckboxList),
			array(new Controls\RadioList),
		);
	}



	/**
	 * @dataProvider dataItemControls
	 *
	 * @param \Nette\Forms\IControl $itemsControl
	 */
	public function testItemsControlLoading_FieldNamePairs(IControl $itemsControl)
	{
		// tested control dependencies
		$entity = new Fixtures\RootEntity("Chuck Norris");
		$container = new EntityContainer($entity);
		$container['children'] = $itemsControl;
		$this->mapper->assign($entity, $container);

		// control mapper will require DAO
		$relatedDao = $this->getMockBuilder('Kdyby\Doctrine\Dao')
			->disableOriginalConstructor()
			->getMock();
		$this->getDoctrine()->setDao(__NAMESPACE__ . '\Fixtures\RelatedEntity', $relatedDao);

		// by default, there is no mapper
		$this->assertNull($this->mapper->getControlMapper($itemsControl));
		$this->mapper->setControlMapper($itemsControl, 'name', 'id');

		// mapper should be closure
		$mapper = $this->mapper->getControlMapper($itemsControl);
		$this->assertInstanceOf('Closure', $mapper);

		// map to control
		$relatedDao->expects($this->atLeastOnce())
			->method('findPairs')
			->with($this->equalTo('name'), $this->equalTo('id'))
			->will($this->returnValue($items = array(1 => 'Lorem')));

		$this->mapper->loadControlItems();
		$this->assertEquals($items, $itemsControl->getItems());
	}



	/**
	 * @dataProvider dataItemControls
	 *
	 * @param \Nette\Forms\IControl $itemsControl
	 */
	public function testItemsControlLoading_FieldNamePairs_FromRelated(IControl $itemsControl)
	{
		// tested control dependencies
		$entity = new Fixtures\RootEntity("Sofia Vergara");
		$entity->children[] = new Fixtures\RelatedEntity($a = "nasoukala své vnady", $entity);
		$entity->children[] = new Fixtures\RelatedEntity($b = "do o dvě čísla menší podprsenky");
		$entity->children[] = new Fixtures\RelatedEntity($c = "a takhle to dopadlo");
		$this->getDao($entity)->save($entity);

		// attach to container & mapper
		$container = new EntityContainer($entity);
		$container['children'] = $itemsControl;
		$this->mapper->assign($entity, $container);
		$this->mapper->setControlMapper($itemsControl, 'name', 'id');

		// load
		$this->mapper->loadControlItems();
		$this->assertEquals(array(
			1 => $a,
			2 => $b,
			3 => $c,
		), $itemsControl->getItems());
	}



	public function testItemsControlLoading_FromCallback()
	{
		$entity = new Fixtures\RootEntity("Roman Štolpa se psychicky zhroutil.");
		$this->attachContainer($container = new EntityContainer($entity));

		$callback = $this->getMock(__NAMESPACE__ . '\CallbackMock', array('__invoke'));
		$callback->expects($this->once())
			->method('__invoke')
			->with($this->equalTo($this->getDao(__NAMESPACE__ . '\Fixtures\RelatedEntity')))
			->will($this->returnValue($items = array(1 => 'Strhaná tvář', 2 => 'slzy v očích')));

		$select = $container->addSelect('children', 'Child')
			->setMapper($callback);

		$this->mapper->loadControlItems();
		$this->assertEquals($items, $select->getItems());
	}



	/**
	 * @return array
	 */
	public function dataSavingItemControlsScalar()
	{
		return array(
			array(new Controls\SelectBox, 2),
			array(new Controls\RadioList, 2),
		);
	}



	/**
	 * @dataProvider dataSavingItemControlsScalar
	 * @group save
	 *
	 * @param \Nette\Forms\IControl $itemsControl
	 * @param int $value
	 */
	public function testItemsControlSaving_FieldNamePairs_FromRelated_Entity(IControl $itemsControl, $value)
	{
		$this->markTestSkipped("not implemented");

		// tested control dependencies
		$entity = new Fixtures\RootEntity("Nikol, ty dlouho sama nebudeš!");
		$entity->children[] = new Fixtures\RelatedEntity($a = "Moravcová rozepnula živůtek");
		$entity->children[] = new Fixtures\RelatedEntity($b = "a větrala");
		$entity->children[] = new Fixtures\RelatedEntity($c = "své neposedné dvojky");
		// intentionally in children to easily persist
		$rootDao = $this->getDao($entity);
		$rootDao->save($entity);

		// attach to container & mapper
		$container = new EntityContainer($entity);
		$container['daddy'] = $itemsControl;
		$this->mapper->assign($entity, $container);
		$this->mapper->setControlMapper($itemsControl, 'name', 'id');

		// load
		$this->mapper->loadControlItems();
		$container['daddy']->setValue($value);
		$this->mapper->save();
		$this->assertInstanceOf(__NAMESPACE__ . '\Fixtures\RelatedEntity', $entity->daddy);
		$this->assertEquals($b, $entity->daddy->name);
	}



	/**
	 * @return array
	 */
	public function dataSavingItemControls()
	{
		return array(
			array(new Controls\MultiSelectBox, array(2)),
			array(new Kdyby\Forms\Controls\CheckboxList, array(1 => FALSE, 2 => TRUE, 3 => FALSE)),
		);
	}



	/**
	 * @dataProvider dataSavingItemControls
	 *
	 * @param \Nette\Forms\IControl $itemsControl
	 * @param array $value
	 */
	public function testItemsControlSaving_FieldNamePairs_FromRelated_Collection(IControl $itemsControl, $value)
	{
		$this->markTestSkipped("not implemented");

		// tested control dependencies
		$entity = new Fixtures\RootEntity("Nikol, ty dlouho sama nebudeš!");
		$entity->children[] = new Fixtures\RelatedEntity($a = "Moravcová rozepnula živůtek");
		$entity->children[] = new Fixtures\RelatedEntity($b = "a větrala");
		$entity->children[] = new Fixtures\RelatedEntity($c = "své neposedné dvojky");
		// intentionally in children to easily persist
		$rootDao = $this->getDao($entity);
		$rootDao->save($entity);

		// attach to container & mapper
		$container = new EntityContainer($entity);
		$container['children'] = $itemsControl;
		$this->mapper->assign($entity, $container);
		$this->mapper->setControlMapper($itemsControl, 'name', 'id');

		// load
		$this->mapper->loadControlItems();
		$container['children']->setValue(array($value));
		$this->mapper->save();
		$this->assertInstanceOf(__NAMESPACE__ . '\Fixtures\RelatedEntity', $entity->daddy);
		$this->assertEquals($b, $entity->daddy->name);
	}



	public function testLoading_Control()
	{
		$entity = new Fixtures\RootEntity("Chuck Tesla");
		$container = new EntityContainer($entity);
		$container->addText('name');

		$this->mapper->assign($entity, $container); // this is done in attached
		$this->mapper->load();

		$this->assertEquals("Chuck Tesla", $container['name']->value);
	}



	public function testLoading_RelatedEntityValues()
	{
		$entity = new Fixtures\RootEntity("Dáda");
		$entity->daddy = new Fixtures\RelatedEntity("Motherfucker");

		$container = new EntityContainer($entity);
		$container->addText('name');
		$container['daddy'] = $daddy = new EntityContainer($this->mapper->getRelated($container, 'daddy'));
		$daddy->addText('name');

		$this->assertEmpty($container['name']->value);
		$this->assertEmpty($daddy['name']->value);

		$this->mapper->assign($entity, $container); // this is done in attached
		$this->mapper->assign($entity->daddy, $daddy); // this is done in attached
		$this->mapper->load(); // form attached to presenter

		$this->assertEquals("Dáda", $container['name']->value);
		$this->assertEquals("Motherfucker", $daddy['name']->value);
	}



	public function testLoading_CreatingRelatedEntity()
	{
		$entity = new Fixtures\RootEntity("Dáda");
		$this->attachContainer($container = new EntityContainer($entity));
		$container->addText('name');

		// when requested relation, mapper tries to create the entity
		$this->assertNull($entity->daddy);
		$daddy = $container->addOne('daddy');
		$this->assertInstanceOf(__NAMESPACE__ . '\Fixtures\RelatedEntity', $entity->daddy);

		$daddy->addText('name');
		$this->mapper->load();

		$this->assertEquals("Dáda", $container['name']->value);
		$this->assertEmpty($daddy['name']->value);
	}



	public function testLoading_OnLoadEvent_Entity()
	{
		$entity = new Fixtures\RootEntity("Jessica Fletcher");
		$container = new EntityContainer($entity);
		$container->addText('name');

		$calls = array();
		$container->onLoad[] = function () use (&$calls) {
			$calls[] = func_get_args();
		};

		$this->mapper->assign($entity, $container);
		$this->mapper->load();

		$this->assertCount(1, $calls);

		$call = current($calls);
		$this->assertEquals(Nette\ArrayHash::from(array('name' => "Jessica Fletcher")), $call[0]);
		$this->assertSame($entity, $call[1]);
	}



	/**
	 * @param \Kdyby\Doctrine\Forms\IObjectContainer $container
	 * @param bool $submitted
	 *
	 * @return \Kdyby\Doctrine\Forms\Form
	 */
	private function attachContainer(Kdyby\Doctrine\Forms\IObjectContainer $container, $submitted = FALSE)
	{
		$form = new Form($this->getDoctrine(), NULL, $this->mapper);
		$form->addSubmit('send');
		if ($submitted !== NULL) {
			$form->setSubmittedBy($submitted !== FALSE ? $form['send'] : NULL);
		}
		$form['entity'] = $container;
		return $form;
	}



	/**
	 * @param \Kdyby\Doctrine\Forms\Form $form
	 *
	 * @return \Nette\Application\UI\Presenter|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function attachForm(Form $form)
	{
		$presenter = $this->getMock('Nette\Application\UI\Presenter', array(), array($this->getContext()));
		$form->setParent($presenter, 'form');
		return $presenter;
	}



	public function testLoading_RelatedCollection()
	{
		$entity = new Fixtures\RootEntity("Agáta Hanychová");
		$entity->children[] = new Fixtures\RelatedEntity("se otrávila jídlem");
		$entity->children[] = new Fixtures\RelatedEntity("Celé dny");
		$entity->children[] = new Fixtures\RelatedEntity("jenom zvrací");

		$form = new Form($this->getDoctrine(), $entity, $this->mapper);
		$form->addText('name');
		$form->addMany('children', function (EntityContainer $container) {
			$container->addText('name');
		});

		// attach to presenter
		$this->attachForm($form);

		// form attached to presenter
		$this->mapper->load();

		// check
		$this->assertCount(3, $form['children']->components);
		$this->assertEquals("Agáta Hanychová", $form['name']->value);
		$this->assertEquals(Nette\ArrayHash::from(array(
			array('name' => "se otrávila jídlem"),
			array('name' => "Celé dny"),
			array('name' => "jenom zvrací"),
		)), $form['children']->getValues());
	}



	public function testSaving_Controls()
	{
		$entity = new Fixtures\RootEntity();
		$container = new EntityContainer($entity);
		$container->addText('name')->setValue("Barbara Falgeová");

		$this->mapper->assign($entity, $container); // this is done in attached
		$this->mapper->loadControlItems();
		$this->mapper->save(); // form attached to presenter

		$this->assertEquals("Barbara Falgeová", $entity->name);
	}



	public function testSaving_RelatedEntityValues()
	{
		$entity = new Fixtures\RootEntity();
		$entity->daddy = new Fixtures\RelatedEntity();

		$container = new EntityContainer($entity);
		$container->addText('name')->setValue("Eva Herzigová");
		$container['daddy'] = $daddy = new EntityContainer($this->mapper->getRelated($container, 'daddy'));
		$daddy->addText('name')->setValue("Gregoriem Marsiajem");

		$this->mapper->assign($entity, $container); // this is done in attached
		$this->mapper->assign($entity->daddy, $daddy);
		$this->mapper->save(); // form attached to presenter

		$this->assertEquals("Eva Herzigová", $entity->name);
		$this->assertEquals("Gregoriem Marsiajem", $entity->daddy->name);
	}



	public function testSaving_OnSaveEvent_Entity()
	{
		$entity = new Fixtures\RootEntity();
		$container = new EntityContainer($entity);
		$container->addText('name')->setValue("Hanka Mašlíková");

		$calls = array();
		$container->onSave[] = function () use (&$calls) {
			$calls[] = func_get_args();
		};

		$this->mapper->assign($entity, $container);
		$this->mapper->save();

		$this->assertCount(1, $calls);

		$call = current($calls);
		$this->assertEquals(Nette\ArrayHash::from(array('name' => "Hanka Mašlíková")), $call[0]);
		$this->assertSame($container, $call[1]);
	}



	/**
	 * @param object $entity
	 *
	 * @return array
	 */
	private function prepareChildrenContainer($entity)
	{
		$id = $this->getDao($entity)->save($entity)->id;

		$form = new Form($this->getDoctrine(), $entity, $this->mapper);
		$form->addText('name');
		$form->addMany('children', function(EntityContainer $container) {
			$container->addText('name');

		})->setEntityFactory(function ($daddy) {
			return new Kdyby\Tests\Doctrine\Forms\Fixtures\RelatedEntity(NULL, $daddy);
		});

		return array($form, $id);
	}



	/**
	 * @return array
	 */
	public function dataSaving_RelatedCollection()
	{
		$entity = new Fixtures\RootEntity("Podívejte se");
		$entity->children[] = new Fixtures\RelatedEntity("na zapomenuté záběry");
		$entity->children[] = new Fixtures\RelatedEntity("nahé Marilyn Monroe");
		$entity->children[] = new Fixtures\RelatedEntity("natočené těsně před smrtí");

		return array(
			array($entity)
		);
	}



	/**
	 * @dataProvider dataSaving_RelatedCollection
	 *
	 * @param \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity $entity
	 */
	public function testSaving_RelatedCollection_UpdatingExisting(Fixtures\RootEntity $entity)
	{
		list($form, $id) = $this->prepareChildrenContainer($entity);

		// attach & save
		$this->submitForm($form, array('children' => array(
			array('id' => 1, 'name' => $a = "Nádherná Vignerová"),
			array('id' => 2, 'name' => $b = "se chystá opustit Ujfalušiho"),
			array('id' => 3, 'name' => $c = "Víme proč")
		), 'name' => $n = "krysy na hotelovém pokoji"));

		// check persisted values
		$this->getDoctrine()->getEntityManager()->clear();
		$entity = $this->getDao($entity)->find($id);

		$this->assertEquals($n, $entity->name);
		$this->assertCount(3, $entity->children);

		$relatedDao = $this->getDao(__NAMESPACE__ . '\Fixtures\RelatedEntity');
		$this->assertEquals($a, $relatedDao->find(1)->name);
		$this->assertEquals($b, $relatedDao->find(2)->name);
		$this->assertEquals($c, $relatedDao->find(3)->name);
	}



	/**
	 * @dataProvider dataSaving_RelatedCollection
	 *
	 * @param \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity $entity
	 */
	public function testSaving_RelatedCollection_Appending(Fixtures\RootEntity $entity)
	{
		list($form, $id) = $this->prepareChildrenContainer($entity);

		// attach & save
		$this->submitForm($form, array('children' => array(
			0 => array('id' => 1, 'name' => $a = "Stydlivka dokonce"),
			1 => array('id' => 2, 'name' => $b = "prozradila detaily"),
			2 => array('id' => 3, 'name' => $c = "soukromého života"),
			3 => array('name' => $d = "ukázala manžela"),
		), 'name' => $n = "Irglová Česku"));

		// check persisted values
		$this->getDoctrine()->getEntityManager()->clear();
		$entity = $this->getDao($entity)->find($id);

		$this->assertEquals($n, $entity->name);
		$this->assertCount(4, $entity->children);

		$relatedDao = $this->getDao(__NAMESPACE__ . '\Fixtures\RelatedEntity');
		$this->assertEquals($a, $relatedDao->find(1)->name);
		$this->assertEquals($b, $relatedDao->find(2)->name);
		$this->assertEquals($c, $relatedDao->find(3)->name);
		$this->assertEquals($d, $relatedDao->find(4)->name);
	}



	/**
	 * @dataProvider dataSaving_RelatedCollection
	 *
	 * @param \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity $entity
	 */
	public function testSaving_RelatedCollection_RemovingAndAddingNew(Fixtures\RootEntity $entity)
	{
		list($form, $id) = $this->prepareChildrenContainer($entity);

		$clickCalls = array();
		$form['children']->setFactory(function(EntityContainer $container) use (&$clickCalls)
		{
			$container->addText('name');
			$container->addSubmit('delete', 'Delete')->onClick[] = function () use (&$clickCalls)
			{
				$clickCalls[] = func_get_args();
			};
			$container['delete']->addRemoveOnClick();
		});

		// attach & save
		$this->submitForm($form, array('children' => array(
			0 => array('id' => 1, 'name' => $a = "víme, kolik bere Svěrák"),
			1 => array('id' => 2, 'name' => $b = "Janžurová, Donutil", 'delete' => 'Delete'),
			2 => array('id' => 3, 'name' => $c = "Trojan nebo Tomicová"),
			3 => array('name' => $d = "ukázala manžela"),
		), 'name' => $n = "To jsou ale platy!"));

		$this->getDoctrine()->getEntityManager()->clear();
		$entity = $this->getDao($entity)->find($id);

		// confirm click
		$this->assertCount(1, $clickCalls);
		$call = reset($clickCalls);
		$this->assertInstanceOf('Nette\Forms\Controls\SubmitButton', $call[0]);
		$this->assertInstanceOf(__NAMESPACE__ . '\Fixtures\RelatedEntity', $call[1]);
		$this->assertInstanceOf('Kdyby\Doctrine\Dao', $call[2]);

		// check persisted values
		$this->assertEquals($n, $entity->name);
		$this->assertCount(3, $entity->children);

		$relatedDao = $this->getDao(__NAMESPACE__ . '\Fixtures\RelatedEntity');
		$this->assertEquals($a, $relatedDao->find(1)->name);
		$this->assertNull($relatedDao->find(2));
		$this->assertEquals($c, $relatedDao->find(3)->name);
		$this->assertEquals($d, $relatedDao->find(4)->name);
	}



	/**
	 * @dataProvider dataSaving_RelatedCollection
	 *
	 * @param \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity $entity
	 */
	public function testSaving_RelatedCollection_RemovingWithoutSignal(Fixtures\RootEntity $entity)
	{
		list($form, $id) = $this->prepareChildrenContainer($entity);

		// attach & save
		$this->submitForm($form, array('children' => array(
			0 => array('id' => 1, 'name' => $a = "víme, kolik bere Svěrák"),
			2 => array('id' => 3, 'name' => $c = "Trojan nebo Tomicová"),
			3 => array('name' => $d = "ukázala manžela"),
		), 'name' => $n = "To jsou ale platy!"));

		$this->getDoctrine()->getEntityManager()->clear();
		$entity = $this->getDao($entity)->find($id);

		// check persisted values
		$this->assertEquals($n, $entity->name);
		$this->assertCount(3, $entity->children);

		$relatedDao = $this->getDao(__NAMESPACE__ . '\Fixtures\RelatedEntity');
		$this->assertEquals($a, $relatedDao->find(1)->name);
		$this->assertNull($relatedDao->find(2));
		$this->assertEquals($c, $relatedDao->find(3)->name);
		$this->assertEquals($d, $relatedDao->find(4)->name);
	}



	public function testSaving_RelatedCollection_CreatingNew()
	{
		$entity = new Fixtures\RootEntity("Sofia Vergara");
		list($form, $id) = $this->prepareChildrenContainer($entity);

		// attach & save
		$this->submitForm($form, array('children' => array(
			0 => array('name' => $a = "Klidně Karel Gott"),
			1 => array('name' => $b = "nebo hvězda filmů pro dospělé"),
			2 => array('name' => $c = "Dolly Buster!"),
		), 'name' => $n = "Nový český prezident ?"));

		$this->getDoctrine()->getEntityManager()->clear();
		$entity = $this->getDao($entity)->find($id);

		// check persisted values
		$this->assertEquals($n, $entity->name);
		$this->assertCount(3, $entity->children);

		$relatedDao = $this->getDao(__NAMESPACE__ . '\Fixtures\RelatedEntity');
		$this->assertEquals($a, $relatedDao->find(1)->name);
		$this->assertEquals($b, $relatedDao->find(2)->name);
		$this->assertEquals($c, $relatedDao->find(3)->name);
	}


	/**
	 * @param \Doctrine\Common\Collections\Collection $collection
	 *
	 * @return object[]
	 */
	private function findNewFromCollection(Doctrine\Common\Collections\Collection $collection)
	{
		$UoW = $this->getDoctrine()->getEntityManager()->getUnitOfWork();
		$new = array();
		foreach ($collection as $item) {
			if ($UoW->getEntityState($item, $UoW::STATE_NEW) === $UoW::STATE_NEW) {
				$new[] = $item;
			}
		}

		return $new;
	}

}
