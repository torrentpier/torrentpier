# Changelog

## 1.0.2

*Sep 2, 2022*

* Allow PHP 8.2, no changes to resources

## 1.0.1

*Jun 18, 2022*

* Stronger future compatibility promise: do not install package on versions without metadata
* 1.0.0 and 1.0.0-alpha1 are now removed so the solver won't be confused

## ~~1.0.0~~ (yanked, was 8ef6383)

*Sep 25, 2021*

* Added ``try_get_resource_type()`` function
* Supported PHP 8.0 resource to object changes:
  * `CurlHandle`
  * `CurlMultiHandle`
  * `CurlShareHandle`
  * `EnchantBroker`
  * `EnchantDictionary`
  * `GdImage`
  * `OpenSSLAsymmetricKey`
  * `OpenSSLCertificate`
  * `OpenSSLCertificateSigningRequest`
  * `Shmop`
  * `AddressInfo`
  * `Socket`
  * `SysvMessageQueue`
  * `SysvSemaphore`
  * `SysvSharedMemory`
  * `XMLParser`
  * `XmlRpcServer`
  * `XMLWriter`
  * `DeflateContext`
  * `InflateContext`
* Supported PHP 8.1 resource to object changes:
  * `finfo`
  * `FTP\Connection`
  * `IMAP\Connection`
  * `LDAP\Connection`
  * `LDAP\Result`
  * `LDAP\ResultEntry`
  * `PgSql\Connection`
  * `PgSql\Result`
  * `PgSql\Lob`
* Proper documentation

## ~~1.0.0-alpha1~~ (yanked, was 827ad87)

*Sep 23, 2021*

Initial proof of concept release

* Supported PHP 5.6 resource to object changes:
  * `GMP`
* Supported PHP 7.2 resource to object changes:
  * `HashContext`
