--
-- Datenbank: `gf_reader`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `feeds`
--

CREATE TABLE IF NOT EXISTS `feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `lastupdate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=112 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `feeds_entries`
--

CREATE TABLE IF NOT EXISTS `feeds_entries` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `guid` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `summary` longtext NOT NULL COMMENT 'zlib compressed',
  PRIMARY KEY (`article_id`),
  UNIQUE KEY `guid` (`guid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=152646 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `feeds_read`
--

CREATE TABLE IF NOT EXISTS `feeds_read` (
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `feeds_subscription`
--

CREATE TABLE IF NOT EXISTS `feeds_subscription` (
  `feedid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `alias` varchar(255) NOT NULL,
  PRIMARY KEY (`feedid`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sticky`
--

CREATE TABLE IF NOT EXISTS `sticky` (
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `view_feed_subscriptions`
--
CREATE TABLE IF NOT EXISTS `view_feed_subscriptions` (
`feedid` int(11)
,`lastupdate` int(11)
,`userid` int(11)
,`feedname` varchar(255)
,`alias` varchar(255)
,`origname` varchar(255)
,`feedurl` varchar(255)
);
-- --------------------------------------------------------

--
-- Struktur des Views `view_feed_subscriptions`
--
DROP TABLE IF EXISTS `view_feed_subscriptions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_feed_subscriptions` AS select `s`.`feedid` AS `feedid`,`f`.`lastupdate` AS `lastupdate`,`s`.`userid` AS `userid`,if((`s`.`alias` <> ''),`s`.`alias`,`f`.`name`) AS `feedname`,`s`.`alias` AS `alias`,`f`.`name` AS `origname`,`f`.`url` AS `feedurl` from (`feeds_subscription` `s` join `feeds` `f` on((`f`.`id` = `s`.`feedid`)));

