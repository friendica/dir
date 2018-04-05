-- Generation Time: Apr 21, 2017 at 03:58 AM

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

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
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `photo`
--

CREATE TABLE IF NOT EXISTS `photo` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile-id` int(11) NOT NULL,
  `data` mediumblob NOT NULL,
  `score` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2516 DEFAULT CHARSET=utf8mb4;

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
  `homepage` char(255) NOT NULL,
  `photo` char(255) NOT NULL,
  `tags` longtext NOT NULL,
  `available` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `censored` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`(250)),
  KEY `nurl` (`nurl`(250)),
  KEY `comm` (`comm`),
  KEY `pdesc` (`pdesc`(250)),
  KEY `locality` (`locality`(250)),
  KEY `region` (`region`(250)),
  KEY `country-name` (`country-name`(250)),
  KEY `homepage` (`homepage`(250))
) ENGINE=MyISAM AUTO_INCREMENT=2518 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sid` char(255) NOT NULL,
  `data` mediumtext NOT NULL,
  `expire` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`(250)),
  KEY `expire` (`expire`)
) ENGINE=MyISAM AUTO_INCREMENT=22917 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `site`
--

CREATE TABLE IF NOT EXISTS `site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL,
  `url` char(255) NOT NULL,
  `version` char(16) NOT NULL,
  `plugins` mediumtext NOT NULL,
  `reg_policy` char(32) NOT NULL,
  `info` mediumtext NOT NULL,
  `admin_name` char(255) NOT NULL,
  `admin_profile` char(255) NOT NULL,
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `site-health`
--

CREATE TABLE IF NOT EXISTS `site-health` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `base_url` varchar(255) NOT NULL,
  `effective_base_url` varchar(255) DEFAULT NULL,
  `health_score` int(11) NOT NULL DEFAULT '0',
  `no_scrape_url` varchar(255) DEFAULT NULL,
  `dt_first_noticed` datetime NOT NULL,
  `dt_last_seen` datetime DEFAULT NULL,
  `dt_last_probed` datetime DEFAULT NULL,
  `dt_last_heartbeat` datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `addons` mediumtext,
  `reg_policy` char(32) DEFAULT NULL,
  `info` mediumtext,
  `admin_name` varchar(255) DEFAULT NULL,
  `admin_profile` varchar(255) DEFAULT NULL,
  `ssl_state` bit(1) DEFAULT NULL,
  `ssl_grade` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `base_url` (`base_url`(250)),
  KEY `health_score` (`health_score`),
  KEY `dt_last_seen` (`dt_last_seen`)
) ENGINE=MyISAM AUTO_INCREMENT=10035 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `site-probe`
--

CREATE TABLE IF NOT EXISTS `site-probe` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_health_id` int(10) UNSIGNED NOT NULL,
  `dt_performed` datetime NOT NULL,
  `request_time` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `site_health_id` (`site_health_id`),
  KEY `dt_performed` (`dt_performed`)
) ENGINE=MyISAM AUTO_INCREMENT=28987 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `site-scrape`
--

CREATE TABLE IF NOT EXISTS `site-scrape` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_health_id` int(10) UNSIGNED NOT NULL,
  `dt_performed` datetime NOT NULL,
  `request_time` int(10) UNSIGNED NOT NULL,
  `scrape_time` int(10) UNSIGNED NOT NULL,
  `photo_time` int(10) UNSIGNED NOT NULL,
  `total_time` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `site_health_id` (`site_health_id`),
  KEY `dt_performed` (`dt_performed`)
) ENGINE=MyISAM AUTO_INCREMENT=177675 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sync-pull-queue`
--

CREATE TABLE IF NOT EXISTS `sync-pull-queue` (
  `url` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sync-push-queue`
--

CREATE TABLE IF NOT EXISTS `sync-push-queue` (
  `url` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sync-targets`
--

CREATE TABLE IF NOT EXISTS `sync-targets` (
  `base_url` varchar(255) CHARACTER SET utf8 NOT NULL,
  `pull` bit(1) NOT NULL DEFAULT b'0',
  `push` bit(1) NOT NULL DEFAULT b'1',
  `dt_last_pull` bigint(20) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`base_url`),
  KEY `push` (`push`),
  KEY `pull` (`pull`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sync-timestamps`
--

CREATE TABLE IF NOT EXISTS `sync-timestamps` (
  `url` varchar(255) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`url`),
  KEY `modified` (`modified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `term` char(255) NOT NULL,
  `nurl` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `term` (`term`(250)),
  KEY `nurl` (`nurl`(250))
) ENGINE=MyISAM AUTO_INCREMENT=101679 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `email` char(255) NOT NULL,
  `password` char(255) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `profile`
--
ALTER TABLE `profile` ADD FULLTEXT KEY `tags` (`tags`);
ALTER TABLE `profile` ADD FULLTEXT KEY `profile-ft` (`name`,`pdesc`,`homepage`,`locality`,`region`,`country-name`,`tags`);
