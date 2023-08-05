<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));


class TS_Client
{
	
	/**
	* Default consturor
	* @param string $api_key
	*/
	function __constructor($api_key)
	{
		$this->API_KEY = $api_key;
	}
	
	/**
	* PHP4 constructor
	* @param string $api_key
	*/
	function TS_Client($api_key)
	{
		$this->__constructor($api_key);
	}
	
	/**
	* Add content to TorrentStream server
	* @access public
	* @param int $zone Zone id
	* @param string $data base64 encoded torrent file
	* @param string $name Content name (UTF-8 encoded)
    * @param int $duration Movie duration (optional)
	* @return string Content id or FALSE on error
	
	<request action="add" key="12345" zone="34">
		<name>content_name_utf8</name>
		<data>torrent_data_base_64</data>
        <duration>duration</duration>
	</request>
	*/
	function add_content($zone_id, $data, $name, $duration = 0)
	{
		// build xml
		$xml = '<?php xml version="1.0" encoding="UTF-8" ?>';
		$xml .= '<request action="add" key="' . $this->API_KEY . '" zone="' . intval($zone_id) . '">';
		$xml .= '<name>' . htmlspecialchars($name) . '</name>';
		$xml .= '<data>' . htmlspecialchars($data) . '</data>';
        $xml .= '<duration>' . intval($duration) . '</duration>';
		$xml .= '</request>';
		
		// post
		$response = $this->_http_post_xml($xml);
		if($response === FALSE) {
			return FALSE;
		}
		return $this->_parse_response($response);
	}
	
	/**
	* Get list of errors
	* @access public
	*/
	function get_errors() {
		return $this->_errors;
	}
	
	/**
	* Send XML over HTTP-POST
	* @access private
	*/
	function _http_post_xml($post_data)
	{
		if(function_exists('curl_init')) {
			return $this->_http_post_curl('text/xml', $post_data);
		}
		else {
			return $this->_http_post_socket('text/xml', $post_data);
		}
	}
	
	/**
	* Send HTTP-POST with cURL library
	* @access private
	*/
	function _http_post_curl($content_type, $post_data)
	{
		// init curl
		$curl = curl_init($this->TS_SERVER_URL);
		if(!$curl) {
			$this->_add_error('Failed to init cURL');
			return FALSE;
		}
		
		// curl options
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($curl, CURLOPT_POST, TRUE);
		
		$http_headers = array(
			'Content-type: ' . $content_type
			);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $http_headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		
		$response = curl_exec($curl);
		if($response === FALSE) {
			$this->_add_error('Request failed: errno=' . curl_errno($curl) . ' description=' . curl_error($curl));
			return FALSE;
		}
		
		return $response;
	}
    
    /**
    * Send HTTP-POST with PHP sockets.
    * @access private
    */
    function _http_post_socket($content_type, $post_data)
    {
        $_func = '_http_post_socket';
        
        $url = parse_url($this->TS_SERVER_URL);
        if($url === FALSE) {
            $this->_add_error($_func . ': parse_url() failed');
            return FALSE;
        }
     
        $connect_timeout = 30;
        $stream_timeout = 30;
        
        // establish connection
        $sock = fsockopen($url['host'], /*$url['port']*/80, $errno, $errmsg, $connect_timeout);
        if( ! $sock) {
            $this->_add_error($_func . ': fsockopen() failed while trying to connect to ' . $url['host'] . ':' . $url['port'] . ': errmsg=' . $errmsg . ' errno=' . $errno);
            return FALSE;
        }
        
        stream_set_timeout($sock, $stream_timeout);
        
        // send headers
        fwrite($sock, 'POST ' . $url['path'] . " HTTP/1.1\r\n");
        fwrite($sock, 'Host: ' . $url['host'] . "\r\n");
        fwrite($sock, 'Content-Type: ' . $content_type . "\r\n");
        fwrite($sock, 'Content-Size: ' . strlen($post_data) . "\r\n");
        fwrite($sock, "Connection: close\r\n\r\n");
        
        // send data
        fwrite($sock, $post_data);
        fwrite($sock, "\r\n\r\n");
        
        // read response
        $response = '';
        while( ! feof($sock)) {
            $response .= fread($sock, 4096);
        }
        
        $stream_info = stream_get_meta_data($sock);
        if($stream_info['timed_out']) {
            $this->_add_error('_http_post_socket: socket timed out');
            return FALSE;
        }
        
        return $response;
    }
	
	/**
	* Parse response from server
	* @access private
	* @param string $response
	* @return string content id or FALSE on error
	
	Successfull response:
	<response>
		<status>accepted</status>
		<id>content_uid</id>
	</response>
	
	Failure response:
	<response>
		<status errorCode="123" error="Error description">failed</status>
	</response>
	*/
	function _parse_response($response)
	{
		$status = '';
		$error_code = 0;
		$error = '';
		$content_uid = '';
		
		if(function_exists('domxml_open_mem')) {
			// load XML
			$doc = domxml_open_mem($response);
			if( ! $doc) {
				$this->_add_error('Failed to parse XML: ' . $response);
				return FALSE;
			}
			
			// find <status>
			$list = $doc->get_elements_by_tagname('status');
			if(count($list) == 0) {
				$this->_add_error('Unable to find <status> in response: ' . $response);
				return FALSE;
			}
			$elem_status = $list[0];
			
			// get <status> value 
			if($elem_status->has_child_nodes()) {
				$text_node = $elem_status->first_child();
				if($text_node->type == XML_TEXT_NODE) {
					$status = $text_node->content;
				}
			}
			
			// check 'errorCode' and 'error' attributes
			if($elem_status->has_attribute("error")) {
				$error = $elem_status->get_attribute("error");
			}
			if($elem_status->has_attribute("errorCode")) {
				$error_code = $elem_status->get_attribute("errorCode");
			}
			
			// find <id>
			$list = $doc->get_elements_by_tagname('id');
			if(count($list) != 0) {
				$elem_id = $list[0];
				
				// get <id> value 
				if($elem_id->has_child_nodes()) {
					$text_node = $elem_id->first_child();
					if($text_node->type == XML_TEXT_NODE) {
						$content_uid = $text_node->content;
					}
				}
			}
		}
		elseif(function_exists('simplexml_load_string')) {
			// load XML
			$doc = simplexml_load_string($response);
			if( ! $doc) {
				$this->_add_error('Failed to parse XML: ' . $response);
				return FALSE;
			}
			
			// find <status>
			if( ! isset($doc->status)) {
				$this->_add_error('Unable to find <status> in response: ' . $response);
				return FALSE;
			}
			
			$elem_status = $doc->status;
			$attributes = $elem_status->attributes();
			
			// get <status> value 
			$status = (string)$doc->status;
			
			// check 'errorCode' and 'error' attributes
			if(isset($attributes['error'])) {
				$error = (string)$attributes['error'];
			}
			if(isset($attributes['errorCode'])) {
				$error_code = (string)$attributes['errorCode'];
			}
			
			// find <id>
			if(isset($doc->id)) {
				// get <id> value 
				$content_uid = (string)$doc->id;
			}
		}
		else {
			$this->_add_error('Cannot parse response from server: XML library must be installed');
			return FALSE;
		}
		
		// return depending on status
		if($status == 'accepted') {
			if(strlen($content_uid) == 0) {
				$this->_add_error('Empty content id in response: ' . $response);
				return FALSE;
			}
			
			return $content_uid;
		}
		elseif($status == 'failed') {
			$this->_add_error('Response failed: errorCode=' . $error_code . ' error=' . $error);
			return FALSE;
		}
		else {
			$this->_add_error('Unknown status (' . $status . ') in response: ' . $response);
			return FALSE;
		}
	}
	
	/**
	* @access private
	*/
	function _add_error($error_message)
	{
		$this->_errors[] = $error_message;
	}
	
	/**
	* @var string Client API key
	* @access private
	*/
	var $API_KEY;
    
	/**
	* @var string URL of the server
	* @access private
	*/
	var $TS_SERVER_URL = 'http://torrentstream.net/api/xml';
	
	/**
	* @var array list of errors
	* @access private
	*/
	var $_errors = array();
}