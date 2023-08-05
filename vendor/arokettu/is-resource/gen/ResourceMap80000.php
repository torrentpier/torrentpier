<?php

namespace Arokettu\IsResource;

/**
 * @internal
 * @generated
 */
final class ResourceMap80000
{
    /**
     * @return array
     */
    public static function map()
    {
        return array(
            'AddressInfo' => array('sockets', 'AddressInfo'),
            'CurlHandle' => array('curl', 'curl'),
            'CurlMultiHandle' => array('curl', 'curl_multi'),
            'CurlShareHandle' => array('curl', 'curl_share'),
            'DeflateContext' => array('zlib', 'zlib.deflate'),
            'EnchantBroker' => array('enchant', 'enchant_broker'),
            'EnchantDictionary' => array('enchant', 'enchant_dict'),
            'GMP' => array('gmp', 'GMP integer'),
            'GdImage' => array('gd', 'gd'),
            'HashContext' => array('hash', 'Hash Context'),
            'InflateContext' => array('zlib', 'zlib.inflate'),
            'OpenSSLAsymmetricKey' => array('openssl', 'OpenSSL key'),
            'OpenSSLCertificate' => array('openssl', 'OpenSSL X.509'),
            'OpenSSLCertificateSigningRequest' => array('openssl', 'OpenSSL X.509 CSR'),
            'Shmop' => array('shmop', 'shmop'),
            'Socket' => array('sockets', 'Socket'),
            'SysvMessageQueue' => array('sysvmsg', 'sysvmsg queue'),
            'SysvSemaphore' => array('sysvsem', 'sysvsem'),
            'SysvSharedMemory' => array('sysvshm', 'sysvshm'),
            'XMLParser' => array('xml', 'xml'),
            'XMLWriter' => array('xmlwriter', 'xmlwriter'),
            'XmlRpcServer' => array('xmlrpc', 'xmlrpc server'),
        );
    }
}
