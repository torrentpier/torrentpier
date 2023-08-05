SET NAMES utf8;

DROP TABLE IF EXISTS `sphinx`.`sphinxtest`;

CREATE TABLE `sphinx`.`sphinxtest` (
`id` BIGINT UNSIGNED NOT NULL auto_increment,
`field1` TEXT,
`field2` TEXT,
`attr1` INT NOT NULL,
`lat` FLOAT NOT NULL,
`long` FLOAT NOT NULL,
`stringattr` VARCHAR(100),
PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `sphinx`.`sphinxtest` (`id`,`field1`,`field2`,`attr1`,`lat`,`long`,`stringattr`) VALUES
(1, 'a', 'bb', 2, 0.35, 0.70, ''),
(2, 'a', 'bb ccc', 4, 0.70, 0.35, ''),
(3, 'a', 'bb ccc dddd', 1, 0.35, 0.70, ''),
(4, 'a bb', 'bb ccc dddd', 5, 0.35, 0.70, ''),
(5, 'bb', 'bb bb ccc dddd', 3, 1.5, 1.5, 'new string attribute'),
('9223372036854775807', 'xx', 'xx', 9000, 150, 150, ''),
(6, _ucs2 x'65e5672c8a9e', '', 0, 0, 0, '');