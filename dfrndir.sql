-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Generation Time: May 15, 2012 at 11:03 PM
-- Server version: 5.1.53
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
--

-- --------------------------------------------------------

--
-- Table structure for table `flag`
--

CREATE TABLE IF NOT EXISTS `flag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `reason` int(11) NOT NULL,
  `total` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `photo`
--

CREATE TABLE IF NOT EXISTS `photo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile-id` int(11) NOT NULL,
  `data` mediumblob NOT NULL,
  `score` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE IF NOT EXISTS `profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL,
  `nurl` char(255) NOT NULL,
  `comm` tinyint(1) NOT NULL DEFAULT '0',
  `pdesc` char(255) NOT NULL,
  `locality` char(255) NOT NULL,
  `region` char(255) NOT NULL,
  `postal-code` char(32) NOT NULL,
  `country-name` char(255) NOT NULL,
  `gender` char(32) NOT NULL,
  `marital` char(255) NOT NULL,
  `homepage` char(255) NOT NULL,
  `photo` char(255) NOT NULL,
  `tags` mediumtext NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `censored` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `nurl` (`nurl`),
  KEY `comm` (`comm`),
  KEY `pdesc` (`pdesc`),
  KEY `locality` (`locality`),
  KEY `region` (`region`),
  KEY `country-name` (`country-name`),
  KEY `homepage` (`homepage`),
  FULLTEXT KEY `tags` (`tags`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sid` char(255) NOT NULL,
  `data` text NOT NULL,
  `expire` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`),
  KEY `expire` (`expire`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `site`
--

CREATE TABLE IF NOT EXISTS `site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL,
  `url` char(255) NOT NULL,
  `version` char(16) NOT NULL,
  `plugins` text NOT NULL,
  `reg_policy` char(32) NOT NULL,
  `info` text NOT NULL,
  `admin_name` char(255) NOT NULL,
  `admin_profile` char(255) NOT NULL,
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `term` char(255) NOT NULL,
  `nurl` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `term` (`term`),
  KEY `nurl` (`nurl`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `email` char(255) NOT NULL,
  `password` char(255) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `site-health`
--

CREATE TABLE IF NOT EXISTS `site-health` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `base_url` varchar(255) NOT NULL,
  `health_score` int(11) NOT NULL DEFAULT 0,
  `no_scrape_url` varchar(255) NULL DEFAULT NULL,
  `dt_first_noticed` datetime NOT NULL,
  `dt_last_seen` datetime NULL DEFAULT NULL,
  `dt_last_probed` datetime NULL DEFAULT NULL,
  `dt_last_heartbeat` datetime NULL DEFAULT NULL,
  `name` varchar(255) NULL DEFAULT NULL,
  `version` varchar(255) NULL DEFAULT NULL,
  `plugins` text NULL DEFAULT NULL,
  `reg_policy` char(32) NULL DEFAULT NULL,
  `info` text NULL DEFAULT NULL,
  `admin_name` varchar(255) NULL DEFAULT NULL,
  `admin_profile` varchar(255) NULL DEFAULT NULL,
  `ssl_state` bit(1) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `base_url` (`base_url`),
  KEY `health_score` (`health_score`),
  KEY `dt_last_seen` (`dt_last_seen`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `site-probe` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_health_id` int(10) unsigned NOT NULL,
  `dt_performed` datetime NOT NULL,
  `request_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `site_health_id` (`site_health_id`),
  KEY `dt_performed` (`dt_performed`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `site-scrape` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_health_id` int(10) unsigned NOT NULL,
  `dt_performed` datetime NOT NULL,
  `request_time` int(10) unsigned NOT NULL,
  `scrape_time` int(10) unsigned NOT NULL,
  `photo_time` int(10) unsigned NOT NULL,
  `total_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `site_health_id` (`site_health_id`),
  KEY `dt_performed` (`dt_performed`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

