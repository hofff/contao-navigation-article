-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************

CREATE TABLE `tl_hofff_navi_art` (

  `page` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',

  `module` int(10) unsigned NOT NULL default '0',
  `article` int(10) unsigned NOT NULL default '0',

  `cssId` varchar(255) NOT NULL default '',
  `cssClass` varchar(255) NOT NULL default '',
  `nosearch` char(1) NOT NULL default '',
  `container` char(1) NOT NULL default '',

  PRIMARY KEY  (`page`, `sorting`),
  KEY `module` (`module`),
  KEY `article` (`article`),

) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `tl_module` (

  `hofff_navi_art_enable` char(1) NOT NULL default '',

) ENGINE=MyISAM DEFAULT CHARSET=utf8;
