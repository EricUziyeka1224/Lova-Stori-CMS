CREATE TABLE `tb_short_url` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `url_asli` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 `short_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
 `hits` int(11) NOT NULL,
 `ditambahkan` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
