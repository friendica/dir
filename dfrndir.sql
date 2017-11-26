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
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `reason` int(11) NOT NULL,
  `total` int(11) NOT NULL DEFAULT '0'
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `photo`
--

CREATE TABLE IF NOT EXISTS `photo` (
  `id` int(10) UNSIGNED NOT NULL,
  `profile-id` int(11) NOT NULL,
  `data` mediumblob NOT NULL,
  `score` float NOT NULL DEFAULT '0'
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE IF NOT EXISTS `profile` (
  `id` int(11) NOT NULL,
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
  `tags` mediumtext NOT NULL,
  `available` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `censored` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sid` char(255) NOT NULL,
  `data` text NOT NULL,
  `expire` int(10) UNSIGNED NOT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `site`
--

CREATE TABLE IF NOT EXISTS `site` (
  `id` int(11) NOT NULL,
  `name` char(255) NOT NULL,
  `url` char(255) NOT NULL,
  `version` char(16) NOT NULL,
  `plugins` text NOT NULL,
  `reg_policy` char(32) NOT NULL,
  `info` text NOT NULL,
  `admin_name` char(255) NOT NULL,
  `admin_profile` char(255) NOT NULL,
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `site-health`
--

CREATE TABLE IF NOT EXISTS `site-health` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `plugins` text,
  `reg_policy` char(32) DEFAULT NULL,
  `info` text,
  `admin_name` varchar(255) DEFAULT NULL,
  `admin_profile` varchar(255) DEFAULT NULL,
  `ssl_state` bit(1) DEFAULT NULL,
  `ssl_grade` varchar(3) DEFAULT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `site-probe`
--

CREATE TABLE IF NOT EXISTS `site-probe` (
  `id` int(10) UNSIGNED NOT NULL,
  `site_health_id` int(10) UNSIGNED NOT NULL,
  `dt_performed` datetime NOT NULL,
  `request_time` int(10) UNSIGNED NOT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `site-scrape`
--

CREATE TABLE IF NOT EXISTS `site-scrape` (
  `id` int(10) UNSIGNED NOT NULL,
  `site_health_id` int(10) UNSIGNED NOT NULL,
  `dt_performed` datetime NOT NULL,
  `request_time` int(10) UNSIGNED NOT NULL,
  `scrape_time` int(10) UNSIGNED NOT NULL,
  `photo_time` int(10) UNSIGNED NOT NULL,
  `total_time` int(10) UNSIGNED NOT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sync-pull-queue`
--

CREATE TABLE IF NOT EXISTS `sync-pull-queue` (
  `url` varchar(255) NOT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sync-push-queue`
--

CREATE TABLE IF NOT EXISTS `sync-push-queue` (
  `url` varchar(255) NOT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sync-targets`
--

CREATE TABLE IF NOT EXISTS `sync-targets` (
  `base_url` varchar(255) NOT NULL,
  `pull` bit(1) NOT NULL DEFAULT b'0',
  `push` bit(1) NOT NULL DEFAULT b'1',
  `dt_last_pull` bigint(20) UNSIGNED DEFAULT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sync-timestamps`
--

CREATE TABLE IF NOT EXISTS `sync-timestamps` (
  `url` varchar(255) NOT NULL,
  `modified` datetime NOT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL,
  `term` char(255) NOT NULL,
  `nurl` char(255) NOT NULL
) DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(11) NOT NULL,
  `email` char(255) NOT NULL,
  `password` char(255) NOT NULL
) DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `flag`
--
ALTER TABLE `flag`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `photo`
--
ALTER TABLE `photo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `nurl` (`nurl`),
  ADD KEY `comm` (`comm`),
  ADD KEY `pdesc` (`pdesc`),
  ADD KEY `locality` (`locality`),
  ADD KEY `region` (`region`),
  ADD KEY `country-name` (`country-name`),
  ADD KEY `homepage` (`homepage`);
ALTER TABLE `profile` ADD FULLTEXT KEY `tags` (`tags`);
ALTER TABLE `profile` ADD FULLTEXT KEY `profile-ft` (`name`,`pdesc`,`homepage`,`locality`,`region`,`country-name`,`tags`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sid` (`sid`),
  ADD KEY `expire` (`expire`);

--
-- Indexes for table `site`
--
ALTER TABLE `site`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site-health`
--
ALTER TABLE `site-health`
  ADD PRIMARY KEY (`id`),
  ADD KEY `base_url` (`base_url`),
  ADD KEY `health_score` (`health_score`),
  ADD KEY `dt_last_seen` (`dt_last_seen`);

--
-- Indexes for table `site-probe`
--
ALTER TABLE `site-probe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_health_id` (`site_health_id`),
  ADD KEY `dt_performed` (`dt_performed`);

--
-- Indexes for table `site-scrape`
--
ALTER TABLE `site-scrape`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_health_id` (`site_health_id`),
  ADD KEY `dt_performed` (`dt_performed`);

--
-- Indexes for table `sync-pull-queue`
--
ALTER TABLE `sync-pull-queue`
  ADD PRIMARY KEY (`url`);

--
-- Indexes for table `sync-push-queue`
--
ALTER TABLE `sync-push-queue`
  ADD PRIMARY KEY (`url`);

--
-- Indexes for table `sync-targets`
--
ALTER TABLE `sync-targets`
  ADD PRIMARY KEY (`base_url`),
  ADD KEY `push` (`push`),
  ADD KEY `pull` (`pull`);

--
-- Indexes for table `sync-timestamps`
--
ALTER TABLE `sync-timestamps`
  ADD PRIMARY KEY (`url`),
  ADD KEY `modified` (`modified`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`id`),
  ADD KEY `term` (`term`),
  ADD KEY `nurl` (`nurl`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `flag`
--
ALTER TABLE `flag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `photo`
--
ALTER TABLE `photo`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `session`
--
ALTER TABLE `session`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `site`
--
ALTER TABLE `site`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `site-health`
--
ALTER TABLE `site-health`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `site-probe`
--
ALTER TABLE `site-probe`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `site-scrape`
--
ALTER TABLE `site-scrape`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;