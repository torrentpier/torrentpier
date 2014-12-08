<?php

namespace Google\ReCaptcha;

if (!defined('BB_ROOT')) die(basename(__FILE__));

const SIGNUP_URL = 'https://www.google.com/recaptcha/admin';
const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify?';
const VERSION = 'php_1.0';

/**
 * A Response is returned from checkAnswer()
 */
class Response
{
	public $success;
	public $errorCodes;
}

class Exception extends \Exception
{
	public function getError()
	{
		echo $this->getMessage();
	}
}

class Client
{
	private $_secret;
	private $_curl_opts;

	/**
	 * Constructor
	 *
	 * @param string $secret shared secret between site and ReCAPTCHA server
	 * @param array $curl_opts array of curl parameters
	 *
	 * @throws Exception
	 */
	public function __construct($secret, array $curl_opts = array())
	{
		if (is_null($secret) || $secret == '') {
			throw new Exception('To use reCAPTCHA you must get an API key from <a href=\'' . SIGNUP_URL . '\'>' . SIGNUP_URL . '</a>');
		}
		$this->_secret = $secret;
		if (!empty($curl_opts)) {
			$this->_curl_opts = $curl_opts;
		}
	}

	/**
	 * Encodes the given data into a query string format
	 *
	 * @param array $data array of string elements to be encoded
	 *
	 * @return string - encoded request
	 */
	private function _encodeQS($data)
	{
		$req = array();
		foreach ($data as $key => $value) {
			$req[] = $key . '=' . urlencode(stripslashes(trim($value)));
		}
		return implode('&', $req);
	}

	/**
	 * Submits an HTTP GET to a reCAPTCHA server
	 *
	 * @param string $path url path to reCAPTCHA server
	 * @param array $data array of parameters to be sent
	 *
	 * @throws Exception
	 * @return array response
	 */
	private function _submitHTTPGet($path, $data)
	{
		$req = $this->_encodeQS($data);
		// Prefer curl
		if (function_exists('curl_version')) {
			$opts = array(
				CURLOPT_HEADER         => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_USERAGENT      => 'ReCaptcha ' . VERSION,
				CURLOPT_AUTOREFERER    => true,
				CURLOPT_CONNECTTIMEOUT => 60,
				CURLOPT_TIMEOUT        => 60,
				CURLOPT_MAXREDIRS      => 5,
				CURLOPT_ENCODING       => '',
			);
			// Check if we got overrides, or extra options (eg. proxy configuration)
			if (is_array($this->_curl_opts) && !empty($this->_curl_opts)) {
				$opts = array_merge($opts, $this->_curl_opts);
			}
			$conn = curl_init($path . $req);
			curl_setopt_array($conn, $opts);
			$response = curl_exec($conn);
			// Handle a connection error
			$errno = curl_errno($conn);
			if ($errno !== 0) {
				throw new Exception('Fatal error while contacting reCAPTCHA. ' . $errno . ': ' . curl_error($conn));
			}
			curl_close($conn);
		} else {
			$response = file_get_contents($path . $req);
		}
		return $response;
	}

	/**
	 * Calls the reCAPTCHA siteverify API to verify whether the user passes test
	 *
	 * @param string $remoteIp IP address of end user
	 * @param string $response response string from reCAPTCHA verification
	 *
	 * @return ReCaptcha\Response
	 */
	public function verifyResponse($remoteIp, $response)
	{
		// Discard empty solution submissions
		if (is_null($response) || strlen($response) == 0) {
			$recaptchaResponse = new Response();
			$recaptchaResponse->success = false;
			$recaptchaResponse->errorCodes = 'missing-input';
			return $recaptchaResponse;
		}

		// Send request
		$getResponse = $this->_submitHttpGet(
			VERIFY_URL,
			array(
				'secret'   => $this->_secret,
				'remoteip' => $remoteIp,
				'v'        => VERSION,
				'response' => $response
			)
		);
		$answers = json_decode($getResponse, true);
		$recaptchaResponse = new Response();

		// Parse answer
		if (trim($answers['success']) == true) {
			$recaptchaResponse->success = true;
		} else {
			$recaptchaResponse->success = false;
			$recaptchaResponse->errorCodes = $answers['error-codes'];
		}

		return $recaptchaResponse;
	}
}