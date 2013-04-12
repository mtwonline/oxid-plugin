CREATE TABLE IF NOT EXISTS `oxtiramizooconfig` (
  `OXID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `OXSHOPID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `OXVARNAME` varchar(64) NOT NULL DEFAULT '',
  `OXVARTYPE` varchar(4) NOT NULL DEFAULT '',
  `OXVARVALUE` blob NOT NULL,
  `OXLASTSYNC` datetime NOT NULL,
  `OXGROUP` varchar(32) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE  `oxtiramizooconfig` ADD PRIMARY KEY (  `OXID` );


CREATE TABLE IF NOT EXISTS `oxtiramizooretaillocation` (
  `OXID` int(11) NOT NULL AUTO_INCREMENT,
  `OXSHOPID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `OXNAME` varchar(64) NOT NULL DEFAULT '',
  `OXAPITOKEN` varchar(4) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE  `oxtiramizooconfig` ADD PRIMARY KEY (  `OXID` );


CREATE TABLE IF NOT EXISTS `oxtiramizooreataillocationconfig` (
  `OXID` int(11) NOT NULL AUTO_INCREMENT,
  `OXSHOPID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `OXVARNAME` varchar(64) NOT NULL DEFAULT '',
  `OXVARTYPE` varchar(4) NOT NULL DEFAULT '',
  `OXVARVALUE` blob NOT NULL,
  `OXLASTSYNC` datetime NOT NULL,
  `OXRETAILLOCATIONID` int(11)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE  `oxtiramizooconfig` ADD PRIMARY KEY (  `OXID` );


