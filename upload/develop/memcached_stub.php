<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class Memcached {

	/**
	 * Libmemcached behavior options.
	 */

	const OPT_HASH = null;

	const OPT_HASH_DEFAULT = null;

	const OPT_HASH_MD5 = null;

	const OPT_HASH_CRC = null;

	const OPT_HASH_FNV1_64 = null;

	const OPT_HASH_FNV1A_64 = null;

	const OPT_HASH_FNV1_32 = null;

	const OPT_HASH_FNV1A_32 = null;

	const OPT_HASH_HSIEH = null;

	const OPT_HASH_MURMUR = null;

	const OPT_DISTRIBUTION = null;

	const OPT_DISTRIBUTION_MODULA = null;

	const OPT_DISTRIBUTION_CONSISTENT = null;

	const OPT_LIBKETAMA_COMPATIBLE = null;

	const OPT_BUFFER_REQUESTS = null;

	const OPT_BINARY_PROTOCOL = null;

	const OPT_NO_BLOCK = null;

	const OPT_TCP_NODELAY = null;

	const OPT_SOCKET_SEND_SIZE = null;

	const OPT_SOCKET_RECV_SIZE = null;

	const OPT_CONNECT_TIMEOUT = null;

	const OPT_RETRY_TIMEOUT = null;

	const OPT_SND_TIMEOUT = null;

	const OPT_RCV_TIMEOUT = null;

	const OPT_POLL_TIMEOUT = null;

	const OPT_SERVER_FAILURE_LIMIT = null;

	const OPT_CACHE_LOOKUPS = null;


	/**
	 * Class options.
	 */
	const OPT_COMPRESSION = null;

	const OPT_PREFIX_KEY = null;


	public function __construct( $persistent_id = '' ) {}

	public function get( $key, $cache_cb = null, &$cas_token = null ) {}

	public function getByKey( $server_key, $key, $cache_cb = null, &$cas_token = null ) {}

	public function getMulti( array $keys, &$cas_tokens = null, $flags = 0 ) {}

	public function getMultiByKey( $server_key, array $keys, &$cas_tokens = null, $flags = 0 ) {}

	public function getDelayed( array $keys, $with_cas = null, $value_cb = null ) {}

	public function getDelayedByKey( $server_key, array $keys, $with_cas = null, $value_cb = null ) {}

	public function fetch( ) {}

	public function fetchAll( ) {}

	public function set( $key, $value, $expiration = 0 ) {}

	public function setByKey( $server_key, $key, $value, $expiration = 0 ) {}

	public function setMulti( array $items, $expiration = 0 ) {}

	public function setMultiByKey( $server_key, array $items, $expiration = 0 ) {}

	public function cas( $token, $key, $value, $expiration = 0 ) {}

	public function casByKey( $token, $server_key, $key, $value, $expiration = 0 ) {}

	public function add( $key, $value, $expiration = 0 ) {}

	public function addByKey( $server_key, $key, $value, $expiration = 0 ) {}

	public function append( $key, $value, $expiration = 0 ) {}

	public function appendByKey( $server_ke, $key, $value, $expiration = 0 ) {}

	public function prepend( $key, $value, $expiration = 0 ) {}

	public function prependByKey( $server_key, $key, $value, $expiration = 0 ) {}

	public function replace( $key, $value, $expiration = 0 ) {}

	public function replaceByKey( $serve_key, $key, $value, $expiration = 0 ) {}

	public function delete( $key, $time = 0 ) {}

	public function deleteByKey( $key, $time = 0 ) {}

	public function increment( $key, $offset = 1) {}

	public function decrement( $key, $offset = 1) {}

	public function getOption( $option ) {}

	public function setOption( $option, $value ) {}

	public function addServer( $host, $port,  $weight = 0 ) {}

	public function addServers( array $servers ) {}

	public function getServerList( ) {}

	public function getServerByKey( $server_key ) {}

	public function flush( $delay = 0 ) {}

	public function getStats( ) {}

	public function getResultCode( ) {}

	public function getResultMessage( ) {}

}

class MemcachedException extends Exception {

	function __construct( $errmsg = "", $errcode  = 0 ) {}

}