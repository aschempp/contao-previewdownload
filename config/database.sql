-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

-- 
-- Table `tl_content`
-- 

CREATE TABLE `tl_content` (
  `previewFile` varchar(255) NOT NULL default '',
  `previewImage` varchar(255) NOT NULL default '',
  `previewTips` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

