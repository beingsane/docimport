CREATE TABLE IF NOT EXISTS `#__docimport_articles` (
  `docimport_article_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `docimport_category_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `fulltext` longtext NOT NULL,
  `meta_description` varchar(2048) DEFAULT NULL,
  `meta_tags` varchar(2048) DEFAULT NULL,
  `last_timestamp` int(11) DEFAULT NULL,
  `enabled` tinyint(3) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT '0',
   `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`docimport_article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__docimport_categories` (
  `docimport_category_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `docimport_vgroup_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `process_plugins` tinyint(3) NOT NULL DEFAULT '0',
  `last_timestamp` bigint(20) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(3) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT '0',
  `language` varchar(255) NOT NULL DEFAULT '*',
  `access` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`docimport_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__docimport_urls` (
  `nonsef` varchar(10240) NOT NULL DEFAULT '',
  `sef` varchar(10240) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;