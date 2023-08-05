<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

error_reporting(false);

/**
 * Curl based HTTP Client
 * Simple but effective OOP wrapper around Curl php lib.
 * Contains common methods needed
 * for getting data from url, setting referrer, credentials,
 * sending post data, managing cookies, etc.
 *
 * Samle usage:
 * $curl = new Curl();
 * $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
 * $curl->set_user_agent($useragent);
 * $curl->store_cookies("/tmp/cookies.txt");
 * $post_data = array('login' => 'pera', 'password' => 'joe');
 * $html_data = $curl->send_post_data(http://www.foo.com/login.php, $post_data);
 */

class Curl
{
	/**
	 * Curl handle
	 *
	 * @access protected
	 * @var resource
	 */
	protected $ch;


	/**
	 * Default curl options
	 *    (more details about each option: http://www.php.net/manual/en/function.curl-setopt-array.php)
	 * @var array
	 * @access protected
	 */
	protected $config = [
		CURLOPT_FAILONERROR    => false,                 //whether to fail if http response code is >=400
		CURLOPT_FOLLOWLOCATION => true,                  //whether to follow any 'Location:..' header from response
		CURLOPT_AUTOREFERER    => true,                  //whether to automatically set referer for http redirections
		CURLOPT_ENCODING       => 'gzip, deflate',       //The contents of the Accept-Encoding header in curl request
		CURLOPT_SSL_VERIFYPEER => false,                 //whether to verify ssl peer's  certificate
		CURLOPT_HEADER         => false,                 //whether to add response headers to the output
		CURLOPT_USERAGENT      => 'CurlHttpClient/v2.0', //default user agent if none is set
		CURLOPT_SSLVERSION     => 1,                     //force cURL to use TLSv1 (prevent it from using SSLv3 ever)
	];

	/**
	 * CurlHttpClient constructor
	 *
	 * @access public
	 */
	public function __construct ()
	{
		$this->init();
	}

	/**
	 * Init new Curl handle
	 *
	 * @access public
	 */
	public function init ()
	{
		//create new curl handle
		$this->ch = curl_init();

		//set options
		curl_setopt_array($this->ch, $this->config);
	}

	/**
	 * Set custom curl option
	 *    (usually not needed to call this directly, advanced users only)
	 *
	 * @param int $opt
	 * @param int $value
	 *
	 * @access public
	 */
	public function setCustomOption ($opt, $value)
	{
		curl_setopt($this->ch, $opt, $value);
	}

	/**
	 * Set client's useragent
	 *
	 * @param string $useragent
	 *
	 * @access public
	 */
	public function setUserAgent ($useragent)
	{
		curl_setopt($this->ch, CURLOPT_USERAGENT, $useragent);
	}

	/**
	 * Set custom referer
	 *
	 * @param string $referer_url
	 *
	 * @access public
	 */
	public function setReferer ($referer_url)
	{
		curl_setopt($this->ch, CURLOPT_REFERER, $referer_url);
	}

	/**
	 * Whether to include response headers in results
	 *
	 * @param boolean true to include response headers, false to suppress them
	 *
	 * @access public
	 */
	public function includeResponseHeaders ($value)
	{
		curl_setopt($this->ch, CURLOPT_HEADER, $value);
	}

	/**
	 * Set username/pass for basic http auth
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @access public
	 */
	public function setCredentials ($username, $password)
	{
		curl_setopt($this->ch, CURLOPT_USERPWD, "$username:$password");
	}

	/**
	 * Set proxy to use for each curl request
	 *
	 * @param string  $proxy_url
	 * @param boolean $socks5 whether to use socks5 [=false]
	 *
	 * @access public
	 */
	public function setProxy ($proxy_url, $socks5 = false)
	{
		curl_setopt($this->ch, CURLOPT_PROXY, $proxy_url);

		//set proxy to use socks5
		if ($socks5)
		{
			curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		}
	}

	/**
	 * Set proxy auth
	 *
	 * @param $proxy_auth
	 */
	public function setProxyAuth ($proxy_auth)
	{
		curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxy_auth);
	}

	/**
	 * Fetch data from passed URL (by using http get method)
	 *
	 * @param string $url
	 * @param string $ip      address to bind (default null)
	 * @param int    $timeout in sec for complete curl operation [=10]
	 *
	 * @return mixed string data returned from url or boolean false if error occured
	 * @access public
	 */
	public function fetchUrl ($url, $ip = null, $timeout = 10)
	{
		//set various curl options first

		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL, $url);

		//set method to get
		curl_setopt($this->ch, CURLOPT_HTTPGET, true);

		// return into a variable rather than displaying it
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

		//bind to specific ip address if it is sent trough arguments
		if ($ip)
		{
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//and finally send curl request
		$result = curl_exec($this->ch);

		if ($this->hasError())
		{
			return false;
		}

		return $result;
	}


	/**
	 * Send post request to target URL
	 *
	 * @param string $url
	 * @param mixed  $postdata (assoc array ie. $foo['post_var_name'] = $value or as string like var=val1&var2=val2)
	 * @param string $ip       IP address to bind to [=null]
	 * @param int    $timeout  Timeout in sec for complete curl operation [=10]
	 *
	 * @return mixed string with data returned from url or boolean false if error occured
	 * @access public
	 */
	public function sendPostData ($url, $postdata, $ip = null, $timeout = 10)
	{
		//set various curl options first

		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL, $url);

		// return into a variable rather than displaying it
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

		//bind to specific ip address if it is sent trough arguments
		if ($ip)
		{
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//set method to post
		curl_setopt($this->ch, CURLOPT_POST, true);

		$post_string = is_array($postdata) ? http_build_query($postdata) : $postdata;

		// set post string
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_string);

		//and finally send curl request
		$result = curl_exec($this->ch);

		if ($this->hasError())
		{
			return false;
		}

		return $result;
	}

	/**
	 * Fetch data from target URL and store it directly into file
	 *
	 * @param string   $url
	 * @param resource $fp      stream resource(ie. fopen)
	 * @param string   $ip      address to bind (default null)
	 * @param int      $timeout in sec for complete curl operation (default 5)
	 *
	 * @return boolean true on success false othervise
	 * @access public
	 */
	public function fetchIntoFile ($url, $fp, $ip = null, $timeout = 5)
	{
		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL, $url);

		//set method to get
		curl_setopt($this->ch, CURLOPT_HTTPGET, true);

		// store data into file rather than displaying it
		curl_setopt($this->ch, CURLOPT_FILE, $fp);

		//bind to specific ip address if it is sent trough arguments
		if ($ip)
		{
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//and finally send curl request
		curl_exec($this->ch);

		if ($this->hasError())
		{
			return false;
		}

		return true;
	}

	/**
	 * Set file location where cookie data will be stored on curl handle close
	 *    and then parsed and send along on new requests
	 *
	 * @param string $cookie_file absolute path to cookie file (must be in writable dir)
	 *
	 * @access public
	 */
	public function storeCookies ($cookie_file)
	{
		//make sure all cookies are stored to $cookie_file when curl handle is closed
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookie_file);

		//The name of the file containing the cookie data
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookie_file);
	}


	/**
	 * Set custom cookie
	 *
	 * @param mixed string or array with key=>value (i.e. array('foo'=>'value'))
	 *
	 * @access public
	 */
	public function setCookie ($cookie)
	{
		//if cookie is sent as key=>value array
		if (is_array($cookie))
		{
			$cookies_data = [];
			foreach ($cookie as $key => $value)
			{
				$cookies_data[] = "{$key}={$value}";
			}

			//and implode (useful if more than one cookie is sent along so we separate them by ;)
			$cookie = implode('; ', $cookies_data);
		}

		curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
	}

	/**
	 * Wraper around curl's getinfo method
	 * http://www.php.net/manual/en/function.curl-getinfo.php
	 *
	 * @param int $opt
	 *
	 * @access public
	 * @return mixed array with all data or sting (depends of $opt param)
	 */
	public function getInfo ($opt = 0)
	{
		return curl_getinfo($this->ch, $opt);
	}

	/**
	 * Get last URL info
	 *    (usefull when original url was redirected to other location)
	 *
	 * @access public
	 * @return string url
	 */
	public function getEffectiveUrl ()
	{
		return $this->getInfo(CURLINFO_EFFECTIVE_URL);
	}

	/**
	 * Get http response code
	 *
	 * @access public
	 * @return int
	 */
	public function getHttpResponseCode ()
	{
		return $this->getInfo(CURLINFO_HTTP_CODE);
	}

	/**
	 * Get http reqeust headers generated with last request
	 *
	 * @access public
	 * @return string
	 */
	public function getRequestHeaders ()
	{
		return $this->getInfo(CURLINFO_HEADER_OUT);
	}

	/**
	 * Total reqeust time in seconds for last transfer
	 *
	 * @access public
	 * @return int
	 */
	public function getRequestDuration ()
	{
		return $this->getInfo(CURLINFO_TOTAL_TIME);
	}

	/**
	 * Return nice formatted last curl error message and error number
	 *
	 * @return string error msg
	 * @access public
	 */
	function getErrorMsg ()
	{
		return "Curl error #" . curl_errno($this->ch) . ": " . curl_error($this->ch);
	}

	/**
	 * Return true if we had an error during last curl request
	 *
	 * @access public
	 * @return boolean
	 */
	protected function hasError ()
	{
		return (curl_errno($this->ch) != 0) ? true : false;
	}

	/**
	 * Close curl session and free resource
	 * Usually no need to call this function directly but in case you do (i.e. to free resources),
	 *  you'll have to call $this->init() in order to recreate curl handle
	 *
	 * @access public
	 */
	public function close ()
	{
		//close curl session and free up resources
		curl_close($this->ch);
	}

	/**
	 * Curl_HTTP_Client destructor
	 *
	 * @access public
	 */
	public function __destruct ()
	{
		$this->close();
	}
}
