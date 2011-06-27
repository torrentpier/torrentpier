<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

/**_______________________________________
 *
 *  FastJSON,
 *	simple and fast Pear Service_JSON encoder/decoder alternative
 *	[http://pear.php.net/pepr/pepr-proposal-show.php?id=198]
 * ---------------------------------------
 * This class is about two time faster than Pear Service_JSON class.
 * This class is probably not powerful as Service_JSON but it has
 * no dependencies and converts correctly ASCII range 0x00 - 0x1F too.
 * There's any string convertion, just regular RFC specific characters are converted
 * into \u00XX string.
 * To don't have problems with other chars try to use utf8_encode($json_encoded_string).
 * To recieve correctly JSON strings from JavaScript use encodeURIComponent then
 * use, if is necessary, utf8_decode before JS to PHP convertion.
 * decode method doesn't returns a standard object class but You can
 * create the corret class directly with FastJSON::convert method
 * and with them You can manage JS Date objects too.
 * ---------------------------------------
 * Summary of static public methods
 *
 * 	convert
 *			extra, special method
 *
 *	decode
 *			converts a valid JSON string
 *			into a native PHP variable
 *
 *	encode
 *			converts a native php variable
 *			into a valid JSON string
 * ---------------------------------------
 *
 * Special FastJSON::convert method Informations
 * _______________________________________
 * --------------------------------------- 
 * This method is used by FastJSON::encode method but should be used
 * to do these convertions too:
 *
 * - JSON string to time() integer:
 *
 *		FastJSON::convert(decodedDate:String):time()
 *
 *	If You recieve a date string rappresentation You
 *	could convert into respective time() integer.
 *	Example:
 *		FastJSON::convert(FastJSON::decode($clienttime));
 *		// i.e. $clienttime = 2006-11-09T14:42:30
 *		// returned time will be an integer useful with gmdate or date
 *		// to create, for example, this string
 *              // Thu Nov 09 2006 14:42:30 GMT+0100 (Rome, Europe)
 *
 * - time() to JSON string:
 *
 *		FastJSON::convert(time():Int32, true:Boolean):JSON Date String format
 *
 *	You could send server time() informations and send them to clients.
 *	Example:
 *		FastJSON::convert(time(), true);
 *		// i.e. 2006-11-09T14:42:30
 *
 * - associative array to generic class:
 *
 *		FastJSON::convert(array(params=>values), new GenericClass):new Instance of GenericClass
 *
 *	With a decoded JSON object You could convert them
 *	into a new instance of your Generic Class.
 *	Example:
 *		class MyClass {
 *			var	$param = "somevalue";
 *			function MyClass($somevar) {
 *				$this->somevar = $somevar;
 *			};
 *			function getVar = function(){
 *				return $this->somevar;
 *			};
 *		};
 *		
 *		$instance = new MyClass("example");
 *		$encoded = FastJSON::encode($instance);
 *		// {"param":"somevalue"}
 *		
 *		$decoded = FastJSON::decode($encoded);
 *		// $decoded instanceof Object	=> true
 *		// $decoded instanceof MyClass	=> false
 *		
 *		$decoded = FastJSON::convert($decoded, new MyClass("example"));
 *		// $decoded instanceof Object	=> true
 *		// $decoded instanceof MyClass	=> true
 *
 *		$decoded->getVar(); // example
 *
 * ---------------------------------------
 *
 * @author		Andrea Giammarchi
 * @site		http://www.devpro.it/
 * @version		0.4 [fixed string convertion problems, add stdClass optional convertion instead of associative array (used by default)]
 * @requires		anything
 * @compatibility	PHP >= 4
 * @license
 * ---------------------------------------
 * 
 * Copyright (c) 2006 - 2007 Andrea Giammarchi
 *
 * Permission is hereby granted, free of charge,
 * to any person obtaining a copy of this software and associated
 * documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * _______________________________________
 */
class FastJSON {

	// public methods

	/**
	 * public static method
	 *
	 *	FastJSON::convert(params:* [, result:Instance]):*
	 *
	 * @param	*		String or Object
	 * @param	Instance	optional new generic class instance if first
	 *				parameter is an object.
	 * @return	*		time() value or new Instance with object parameters.
	 *
	 * @note	please read Special FastJSON::convert method Informations
	 */
	function convert($params, $result = null){
		switch(gettype($params)){
			case	'array':
					$tmp = array();
					foreach($params as $key => $value) {
						if(($value = FastJSON::encode($value)) !== '')
							array_push($tmp, FastJSON::encode(strval($key)).':'.$value);
					};
					$result = '{'.implode(',', $tmp).'}';
					break;
			case	'boolean':
					$result = $params ? 'true' : 'false';
					break;
			case	'double':
			case	'float':
			case	'integer':
					$result = $result !== null ? strftime('%Y-%m-%dT%H:%M:%S', $params) : strval($params);
					break;
			case	'NULL':
					$result = 'null';
					break;
			case	'string':
					$i = create_function('&$e, $p, $l', 'return intval(substr($e, $p, $l));');
					if(preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $params))
						$result = mktime($i($params, 11, 2), $i($params, 14, 2), $i($params, 17, 2), $i($params, 5, 2), $i($params, 9, 2), $i($params, 0, 4));
					break;
			case	'object':
					$tmp = array();
					if(is_object($result)) {
						foreach($params as $key => $value)
							$result->$key = $value;
					} else {
						$result = get_object_vars($params);
						foreach($result as $key => $value) {
							if(($value = FastJSON::encode($value)) !== '')
								array_push($tmp, FastJSON::encode($key).':'.$value);
						};
						$result = '{'.implode(',', $tmp).'}';
					}
					break;
		}
		return $result;
	}

	/**
	 * public method
	 *
	 *	FastJSON::decode(params:String[, useStdClass:Boolean]):*
	 *
	 * @param	String	valid JSON encoded string
	 * @param	Bolean	uses stdClass instead of associative array if params contains objects (default false)
	 * @return	*	converted variable or null
	 *				is params is not a JSON compatible string.
	 * @note	This method works in an optimist way. If JSON string is not valid
	 * 		the code execution will die using exit.
	 *		This is probably not so good but JSON is often used combined with
	 *		XMLHttpRequest then I suppose that's better more protection than
	 *		just some WARNING.
	 *		With every kind of valid JSON string the old error_reporting level
	 *		and the old error_handler will be restored.
	 *
	 * @example
	 *		FastJSON::decode('{"param":"value"}'); // associative array
	 *		FastJSON::decode('{"param":"value"}', true); // stdClass
	 *		FastJSON::decode('["one",two,true,false,null,{},[1,2]]'); // array
	 */
	function decode($encode, $stdClass = false){
		$pos = 0;
		$slen = is_string($encode) ? strlen($encode) : null;
		if($slen !== null) {
			$error = error_reporting(0);
			set_error_handler(array('FastJSON', '__exit'));
			$result = FastJSON::__decode($encode, $pos, $slen, $stdClass);
			error_reporting($error);
			restore_error_handler();
		}
		else
			$result = null;
		return $result;
	}

	/**
	 * public method
	 *
	 *	FastJSON::encode(params:*):String
	 *
	 * @param	*		Array, Boolean, Float, Int, Object, String or NULL variable.
	 * @return	String		JSON genric object rappresentation
	 *				or empty string if param is not compatible.
	 *
	 * @example
	 *		FastJSON::encode(array(1,"two")); // '[1,"two"]'
	 *
	 *		$obj = new MyClass();
	 *		obj->param = "value";
	 *		obj->param2 = "value2";
	 *		FastJSON::encode(obj); // '{"param":"value","param2":"value2"}'
	 */
	function encode($decode){
		$result = '';
		switch(gettype($decode)){
			case	'array':
					if(!count($decode) || array_keys($decode) === range(0, count($decode) - 1)) {
						$keys = array();
						foreach($decode as $value) {
							if(($value = FastJSON::encode($value)) !== '')
								array_push($keys, $value);
						}
						$result = '['.implode(',', $keys).']';
					}
					else
						$result = FastJSON::convert($decode);
					break;
			case	'string':
					$replacement = FastJSON::__getStaticReplacement();
					$result = '"'.str_replace($replacement['find'], $replacement['replace'], $decode).'"';
					break;
			default:
					if(!is_callable($decode))
						$result = FastJSON::convert($decode);
					break;
		}
		return $result;
	}

	// private methods, uncommented, sorry
	function __getStaticReplacement(){
		static $replacement = array('find'=>array(), 'replace'=>array());
		if($replacement['find'] == array()) {
			foreach(array_merge(range(0, 7), array(11), range(14, 31)) as $v) {
				$replacement['find'][] = chr($v);
				$replacement['replace'][] = "\\u00".sprintf("%02x", $v);
			}
			$replacement['find'] = array_merge(array(chr(0x5c), chr(0x2F), chr(0x22), chr(0x0d), chr(0x0c), chr(0x0a), chr(0x09), chr(0x08)), $replacement['find']);
			$replacement['replace'] = array_merge(array('\\\\', '\\/', '\\"', '\r', '\f', '\n', '\t', '\b'), $replacement['replace']);
		}	
		return $replacement;
	}
	function __decode(&$encode, &$pos, &$slen, &$stdClass){
		switch($encode{$pos}) {
			case 't':
				$result = true;
				$pos += 4;
				break;
			case 'f':
				$result = false;
				$pos += 5;
				break;
			case 'n':
				$result = null;
				$pos += 4;
				break;
			case '[':
				$result = array();
				++$pos;
				while($encode{$pos} !== ']') {
					array_push($result, FastJSON::__decode($encode, $pos, $slen, $stdClass));
					if($encode{$pos} === ',')
						++$pos;
				}
				++$pos;
				break;
			case '{':
				$result = $stdClass ? new stdClass : array();
				++$pos;
				while($encode{$pos} !== '}') {
					$tmp = FastJSON::__decodeString($encode, $pos);
					++$pos;
					if($stdClass)
						$result->$tmp = FastJSON::__decode($encode, $pos, $slen, $stdClass);
					else
						$result[$tmp] = FastJSON::__decode($encode, $pos, $slen, $stdClass);
					if($encode{$pos} === ',')
						++$pos;
				}
				++$pos;
				break;
			case '"':
				switch($encode{++$pos}) {
					case '"':
						$result = "";
						break;
					default:
						$result = FastJSON::__decodeString($encode, $pos);
						break;
				}
				++$pos;
				break;
			default:
				$tmp = '';
				preg_replace('/^(\-)?([0-9]+)(\.[0-9]+)?([eE]\+[0-9]+)?/e', '$tmp = "\\1\\2\\3\\4"', substr($encode, $pos));
				if($tmp !== '') {
					$pos += strlen($tmp);
					$nint = intval($tmp);
					$nfloat = floatval($tmp);
					$result = $nfloat == $nint ? $nint : $nfloat;
				}
				break;
		}
		return $result;
	}
	function __decodeString(&$encode, &$pos) {
		$replacement = FastJSON::__getStaticReplacement();
		$endString = FastJSON::__endString($encode, $pos, $pos);
		$result = str_replace($replacement['replace'], $replacement['find'], substr($encode, $pos, $endString));
		$pos += $endString;
		return $result;
	}
	function __endString(&$encode, $position, &$pos) {
		do {
			$position = strpos($encode, '"', $position + 1);
		}while($position !== false && FastJSON::__slashedChar($encode, $position - 1));
		if($position === false)
			trigger_error('', E_USER_WARNING);
		return $position - $pos;
	}
	function __exit($str, $a, $b) {
		exit($a.'FATAL: FastJSON decode method failure [malicious or incorrect JSON string]');
	}
	function __slashedChar(&$encode, $position) {
		$pos = 0;
		while($encode{$position--} === '\\')
			$pos++;
		return $pos % 2;
	}
}
    
?>
