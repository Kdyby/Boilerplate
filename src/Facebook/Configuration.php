<?php

namespace Facebook;

use Nette;
use Nette\Http\UrlScript;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Configuration extends Nette\Object
{

	/**
	 * Signed Request Algorithm.
	 */
	const SIGNED_REQUEST_ALGORITHM = 'HMAC-SHA256';

	/**
	 * The Application ID.
	 * @var string
	 */
	public $appId;

	/**
	 * The Application App Secret.
	 * @var string
	 */
	public $appSecret;

	/**
	 * Indicates if the CURL based @ syntax for file uploads is enabled.
	 * @var boolean
	 */
	public $fileUploadSupport = FALSE;

	/**
	 * Indicates if we trust HTTP_X_FORWARDED_* headers.
	 * @var boolean
	 */
	public $trustForwarded = FALSE;

	/**
	 * Maps aliases to Facebook domains.
	 * @var array
	 */
	public $domains = array(
		'api' => 'https://api.facebook.com/',
		'api_video' => 'https://api-video.facebook.com/',
		'api_read' => 'https://api-read.facebook.com/',
		'dialog' => 'https://www.facebook.com/dialog/',
		'graph' => 'https://graph.facebook.com/',
		'graph_video' => 'https://graph-video.facebook.com/',
		'www' => 'https://www.facebook.com/',
	);

	/**
	 * List of query parameters that get automatically dropped when rebuilding
	 * the current URL.
	 * @var array
	 */
	public $dropQueryParams = array(
		'code',
		'state',
		'signed_request',
	);

	/**
	 * @var array
	 */
	public $readOnlyCalls = array(
		'admin.getallocation' => 1,
		'admin.getappproperties' => 1,
		'admin.getbannedusers' => 1,
		'admin.getlivestreamvialink' => 1,
		'admin.getmetrics' => 1,
		'admin.getrestrictioninfo' => 1,
		'application.getpublicinfo' => 1,
		'auth.getapppublickey' => 1,
		'auth.getsession' => 1,
		'auth.getsignedpublicsessiondata' => 1,
		'comments.get' => 1,
		'connect.getunconnectedfriendscount' => 1,
		'dashboard.getactivity' => 1,
		'dashboard.getcount' => 1,
		'dashboard.getglobalnews' => 1,
		'dashboard.getnews' => 1,
		'dashboard.multigetcount' => 1,
		'dashboard.multigetnews' => 1,
		'data.getcookies' => 1,
		'events.get' => 1,
		'events.getmembers' => 1,
		'fbml.getcustomtags' => 1,
		'feed.getappfriendstories' => 1,
		'feed.getregisteredtemplatebundlebyid' => 1,
		'feed.getregisteredtemplatebundles' => 1,
		'fql.multiquery' => 1,
		'fql.query' => 1,
		'friends.arefriends' => 1,
		'friends.get' => 1,
		'friends.getappusers' => 1,
		'friends.getlists' => 1,
		'friends.getmutualfriends' => 1,
		'gifts.get' => 1,
		'groups.get' => 1,
		'groups.getmembers' => 1,
		'intl.gettranslations' => 1,
		'links.get' => 1,
		'notes.get' => 1,
		'notifications.get' => 1,
		'pages.getinfo' => 1,
		'pages.isadmin' => 1,
		'pages.isappadded' => 1,
		'pages.isfan' => 1,
		'permissions.checkavailableapiaccess' => 1,
		'permissions.checkgrantedapiaccess' => 1,
		'photos.get' => 1,
		'photos.getalbums' => 1,
		'photos.gettags' => 1,
		'profile.getinfo' => 1,
		'profile.getinfooptions' => 1,
		'stream.get' => 1,
		'stream.getcomments' => 1,
		'stream.getfilters' => 1,
		'users.getinfo' => 1,
		'users.getloggedinuser' => 1,
		'users.getstandardinfo' => 1,
		'users.hasapppermission' => 1,
		'users.isappuser' => 1,
		'users.isverified' => 1,
		'video.getuploadlimits' => 1
	);



	/**
	 * Configuration of Facebook application.
	 *
	 * @param string $appId the application ID
	 * @param string $secret the application secret
	 * @param bool $fileUpload (optional) boolean indicating if file uploads are enabled
	 * @param bool $trustForwarded
	 */
	public function __construct($appId, $secret, $fileUpload = FALSE, $trustForwarded = FALSE)
	{
		$this->appId = $appId;
		$this->appSecret = $secret;
		$this->fileUploadSupport = $fileUpload;
		$this->trustForwarded = $trustForwarded;
	}



	/**
	 * Constructs and returns the name of the cookie that
	 * potentially houses the signed request for the app user.
	 * The cookie is not set by the BaseFacebook class, but
	 * it may be set by the JavaScript SDK.
	 *
	 * @return string the name of the cookie that would house the signed request value.
	 */
	public function getSignedRequestCookieName()
	{
		return 'fbsr_' . $this->appId;
	}



	/**
	 * Constructs and returns the name of the coookie that potentially contain
	 * metadata. The cookie is not set by the BaseFacebook class, but it may be
	 * set by the JavaScript SDK.
	 *
	 * @return string the name of the cookie that would house metadata.
	 */
	public function getMetadataCookieName()
	{
		return 'fbm_' . $this->appId;
	}



	/**
	 * Returns the access token that should be used for logged out
	 * users when no authorization code is available.
	 *
	 * @return string The application access token, useful for gathering public information about users and applications.
	 */
	public function getApplicationAccessToken()
	{
		return $this->appId . '|' . $this->appSecret;
	}



	/**
	 * Build the URL for given domain alias, path and parameters.
	 *
	 * @param string $name The name of the domain
	 * @param string $path Optional path (without a leading slash)
	 * @param array $params Optional query parameters
	 *
	 * @return UrlScript The URL for the given parameters
	 */
	public function createUrl($name, $path = NULL, $params = array())
	{
		$url = new UrlScript($this->domains[$name]);
		$url->setPath('/' . ltrim($path, '/'));
		$url->appendQuery(array_map(function ($param) {
			return $param instanceof UrlScript ? (string)$param : $param;
		}, $params));
		return $url;
	}



	/**
	 * Build the URL for api given parameters.
	 *
	 * @param $method String the method name.
	 * @return UrlScript The URL for the given parameters
	 */
	public function getApiUrl($method)
	{
		$name = 'api';
		if (isset($this->readOnlyCalls[strtolower($method)])) {
			$name = 'api_read';

		} else if (strtolower($method) === 'video.upload') {
			$name = 'api_video';
		}

		return $this->createUrl($name, 'restserver.php');
	}

}
