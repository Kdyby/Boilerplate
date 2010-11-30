<?php

$q = QuerySQL::create()->name->equals('HosipLan')->roles->contains('admin');
$q = QuerySQL::create()->name->equals('HosipLan')->roles->containsId(1);


$q = Query::create()->name->equals('HosipLan');
$roles = $q->inner('roles');
$roles->containing('admin');
$roles->containingId(1);

$roles = Query::create()->username->equals('HosipLan')->inner('roles');
$roles->containing('admin');
$roles->containingId(1);


$q = QuerySQL::create()->name->equals('HosipLan')
		->and(QuerySQL::aggregate('roles')->name->equals('admin'))
		->and(QuerySQL::composite('contact')->city->equals('Brno'));

$q = QuerySQL::create()->name->equals('HosipLan')
		->and(QuerySQL::aggregateKey('roles')->id->equals(1))
		->and(QuerySQL::composite('contact')->city->equals('Brno'));


$q = Query::create()->name->equals('HosipLan')
		->and(Query::has('roles')->name->equals('admin'))
		->and(Query::is('contact')->city->equals('Brno'));

$q = Query::create()->name->equals('HosipLan')
		->and(Query::has('roles')->id->equals(1))
		->and(Query::is('contact')->city->equals('Brno'));


$q = Query::create()->name->equals('HosipLan')
		->and(Query::$roles->name->equals('admin'))
		->and(Query::$contact->city->equals('Brno'));


$q = Query::create()->name->is('HosipLan')->gender->is('male')
		->and(Query::create('roles')->name->equals('admin'))
		->and(Query::create('contact')->city->equals('Brno'));

$q = Query::create()->name->is('HosipLan')
		->and(Query::create('roles')->id->in(array(1,2,3,4,5)))
		->and(Query::create('contact')->city->equals('Brno'));


$q = Query::create()->name->is('HosipLan')->gender->is('male')
		->and('roles', Query::create()->name->equals('admin'))
		->and('contact', Query::create()->city->equals('Brno'));

$q = Query::find(Query::create()->name->is('HosipLan'))
		->or(Query::create()->name->is('Filip'))
		->and('roles', Query::create()->name->equals('admin'))
		->and('contact', Query::create()->city->equals('Brno'));


//$q = Query::create()->name->is('HosipLan')->gender->is('male')
//		->and('roles', Query::create()->name->equals('admin'))
//		->or('contact', Query::create()->city->equals('Brno'));



