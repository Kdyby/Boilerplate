<?php

namespace Kdyby\Extension\Social\Facebook;

use Nette;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Json;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SignedRequest extends Nette\Object
{

	/**
	 * Parses a signed_request and validates the signature.
	 *
	 * @param string $signedRequest A signed token
	 * @param string $appSecret
	 *
	 * @return array The payload inside it or null if the sig is wrong
	 */
	public static function decode($signedRequest, $appSecret)
	{
		list($encoded_sig, $payload) = explode('.', $signedRequest, 2);

		// decode the data
		$sig = Helpers::base64UrlDecode($encoded_sig);
		$data = Json::decode(Helpers::base64UrlDecode($payload), Json::FORCE_ARRAY);

		if (strtoupper($data['algorithm']) !== Configuration::SIGNED_REQUEST_ALGORITHM) {
			Debugger::log('Unknown algorithm. Expected ' . Configuration::SIGNED_REQUEST_ALGORITHM, 'facebook');
			return null;
		}

		// check sig
		$expected_sig = hash_hmac('sha256', $payload, $appSecret, $raw = true);
		if ($sig !== $expected_sig) {
			Debugger::log('Bad Signed JSON signature!', 'facebook');
			return null;
		}

		return $data;
	}



	/**
	 * Makes a signed_request blob using the given data.
	 *
	 * @param array $data The data array.
	 * @param string $appSecret
	 * @throws InvalidArgumentException
	 * @return string The signed request.
	 */
	public static function encode($data, $appSecret)
	{
		if (!is_array($data)) {
			throw new InvalidArgumentException('makeSignedRequest expects an array. Got: ' . print_r($data, true));
		}

		$data['algorithm'] = Configuration::SIGNED_REQUEST_ALGORITHM;
		$data['issued_at'] = time();

		$b64 = Helpers::base64UrlEncode(Json::encode($data));
		$raw_sig = hash_hmac('sha256', $b64, $appSecret, $raw = true);
		$sig = Helpers::base64UrlEncode($raw_sig);

		return $sig . '.' . $b64;
	}

}
