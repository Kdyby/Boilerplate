<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Loaders;

use Nette;
use Nette\Loaders\LimitedScope;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class KdybyLoader extends Nette\Loaders\AutoLoader
{
	/** @var KdybyLoader */
	private static $instance;

	/** @var array */
	public $list = array(
		//core
		'kdyby\configurator' => '/Environment/Configurator.php',
		'kdyby\confighooks' => '/Environment/ConfigHooks.php',
		'kdyby\component\helpers' => "/Components/Helpers.php",
		'kdyby\control\basecontrol' => '/Control/BaseControl.php',
		'kdyby\control\lookoutcontrol' => '/Control/LookoutControl.php',
		'kdyby\filestorage' => '/Caching/FileStorage.php',
		'kdyby\filesystem' => '/Tools/FileSystem.php',
		'kdyby\form\baseform' => '/Forms/BaseForm.php',
		'kdyby\form\control\checkboxlist' => '/Forms/Controls/CheckboxList.php',
		'kdyby\gateway\gateway' => '/Gateway/Gateway.php',
		'kdyby\gateway\iadapter' => '/Gateway/Interfaces/IAdapter.php',
		'kdyby\gateway\igateway' => '/Gateway/Interfaces/IGateway.php',
		'kdyby\gateway\igatewayauthenticator' => '/Gateway/Interfaces/IGatewayAuthenticator.php',
		'kdyby\gateway\igateways' => '/Gateway/Interfaces/IGateways.php',
		'kdyby\gateway\irequest' => '/Gateway/Interfaces/IRequest.php',
		'kdyby\gateway\iresponse' => '/Gateway/Interfaces/IResponse.php',
		'kdyby\gateway\isecuredgateway' => '/Gateway/Interfaces/ISecuredGateway.php',
		'kdyby\gateway\isecuredrequest' => '/Gateway/Interfaces/ISecuredRequest.php',
		'kdyby\gateway\iservice' => '/Gateway/Interfaces/IService.php',
		'kdyby\gateway\notauthenticatedexception' => '/Gateway/NotAuthenticatedException.php',
		'kdyby\gateway\protocol\soap' => '/Gateway/Protocols/Soap.php',
		'kdyby\gateway\protocol\iprotocol' => '/Gateway/Interfaces/IProtocol.php',
		'kdyby\gateway\request' => '/Gateway/Request.php',
		'kdyby\gateway\response' => '/Gateway/Response.php',
		'kdyby\gateway\securedgateway' => '/Gateway/SecuredGateway.php',
		'kdyby\gateway\service' => '/Gateway/Service.php',
		'kdyby\identity' => '/Security/Identity.php',
		'kdyby\presenter\base' => '/Presenters/Base.php',
		'kdyby\presenterinfo' => '/Tools/PresenterTree/PresenterInfo.php',
		'kdyby\presentertree' => '/Tools/PresenterTree/PresenterTree.php',
		'kdyby\security\applicationlock' => '/Security/ApplicationLock.php',
		'kdyby\security\authenticator' => '/Security/Authenticator.php',
		'kdyby\security\user' => '/Security/User.php',
		'kdyby\tools\logicdelegator' => '/Kdyby/Tools/LogicDelegator.php',
		'kdyby\tools\presentergenerator' => '/Kdyby/Tools/PresenterGenerator.php',
		'kdyby\tools\modeltools' => '/Kdyby/Tools/ModelTools.php',
		'kdyby\web\httphelpers' => '/Web/HttpHelpers.php',

		//components
		'kdyby\component\headjs' => "/Components/Headjs/Headjs.php",
		'kdyby\component\tree' => "/Components/Tree/Tree.php",

		// doctrine
		'kdyby\application\databasemanager' => '/Doctrine/DatabaseManager.php',
		'kdyby\doctrine\cache' => '/Doctrine/Cache.php',
		'kdyby\doctrine\factory' => '/Doctrine/Factory.php',
		'kdyby\entities\baseentity' => '/Doctrine/BaseEntity.php',
		'kdyby\entities\baseidentifiedentity' => '/Doctrine/BaseIdentifiedEntity.php',
		'nella\doctrine\panel' => '/Doctrine/Panel.php'
	);



	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return KdybyLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}



	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim(strtolower($type), '\\');
		if (isset($this->list[$type])) {
			LimitedScope::load(KDYBY_DIR . $this->list[$type]);
			self::$count++;
		}
	}

}