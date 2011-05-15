<?php

namespace Kdyby\GoogleData\Analytics;

use DateTime;
use Nette;
use Kdyby;



class Connection extends Nette\Object
{

	/** @var string */
	private $email;

	/** @var string */
	private $password;

	/** @var string */
	private $profileId;



	/**
	 * @param string $email
	 * @param string $password
	 */
	public function __construct($email, $password)
	{
		$this->email = $email;
		$this->password = $password;
	}



	/**
	 * @param string $id (format: 'ga:1234')
	 */
	public function setProfileId($id)
	{
		//look for a match for the pattern ga:XXXXXXXX, of up to 10 digits
		if (Nette\Utils\Strings::match($id, '/^ga:\d{1,10}/')) {
			throw new GoogleAnalyticsException('Invalid GA Profile ID set. The format should ga:XXXXXX, where XXXXXX is your profile number');
		}

		$this->profileId = $id;
	}



	/**
	* Authenticate the email and password with Google, and set the $_authCode return by Google
	*
	* @param none
	* @return none
	*/
	public function authenticate(Analytics $analytics)
	{
		$response = $analytics->sendRequest(new Request(array(
			'accountType' => 'GOOGLE',
			'Email' => $this->email,
			'Passwd' => $this->passwd,
			'service' => 'analytics',
			'source' => 'askaboutphp-v01'
		)));

		$response = $this->_postTo("https://www.google.com/accounts/ClientLogin", $postdata);
		//process the response;
		if ($response) {
			preg_match('/Auth=(.*)/', $response, $matches);
			if(isset($matches[1])) {
				$this->_authCode = $matches[1];
				return TRUE;
			}
		}
	}

	/**
	* Performs the curl calls to the $url specified.
	*
	* @param string $url
	* @param array $data - specify the data to be 'POST'ed to $url
	* @param array $header - specify any header information
	* @return $response from submission to $url
	*/
	public function _postTo($url, $data=array(), $header=array()) {

		//check that the url is provided
		if (!isset($url)) {
			return FALSE;
		}

		//send the data by curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		if (count($data)>0) {
			//POST METHOD
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		} else {
			$header[] = "application/x-www-form-urlencoded";
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		$response = curl_exec($ch);
		$info = curl_getinfo($ch);

		curl_close($ch);

		//print_r($info);
		//print $response;
		if($info['http_code'] == 200) {
			return $response;
		} elseif ($info['http_code'] == 400) {
			throw new GoogleAnalyticsException('Bad request - '.$response);
		} elseif ($info['http_code'] == 401) {
			throw new GoogleAnalyticsException('Permission Denied - '.$response);
		} else {
			return FALSE;
		}

	}

}