DROP TABLE IF EXISTS `groups`;

--
-- Table structure for table 'groups'
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `cms` tinyint(3) unsigned DEFAULT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table 'groups'
--

INSERT INTO `groups` (`id`, `name`, `title`, `cms`, `permissions`) VALUES
(1, 'admin', 'Administrator', 1, ''),
(2, 'manager', 'Manager', 1, 'a:30:{i:0;s:3:"cms";i:1;s:16:"cms/entries/list";i:2;s:16:"cms/entry/create";i:3;s:14:"cms/entry/edit";i:4;s:14:"cms/categories";i:5;s:14:"cms/pages/list";i:6;s:15:"cms/page/create";i:7;s:13:"cms/page/edit";i:8;s:12:"cms/comments";i:9;s:23:"cms/filemanager/uploads";i:10;s:14:"cms/users/list";i:11;s:13:"cms/user/edit";i:12;s:24:"cms/user/change_password";i:13;s:15:"cms/user/groups";i:14;s:15:"cms/groups/list";i:15;s:15:"cms/groups/edit";i:16;s:17:"cms/groups/create";i:17;s:22:"cms/groups/access_list";i:18;s:22:"cms/groups/permissions";i:19;s:15:"cms/user/create";i:20;s:25:"cms/components/navigation";i:21;s:22:"cms/components/widgets";i:22;s:23:"cms/components/packages";i:23;s:22:"cms/components/editors";i:24;s:26:"cms/appearance/themes/site";i:25;s:25:"cms/appearance/themes/cms";i:26;s:12:"cms/settings";i:27;s:10:"cms/backup";i:28;s:8:"cms/cron";i:29;s:17:"cms/cache-general";}'),
(3, 'members', 'General User', NULL, '');



DROP TABLE IF EXISTS `users`;

--
-- Table structure for table 'users'
--

CREATE TABLE IF NOT EXISTS `users` (
  id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(80) DEFAULT NULL,
  `salt` varchar(40) DEFAULT NULL,
  `cms` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `activation_code` varchar(40) DEFAULT NULL,
  `forgotten_password_code` varchar(40) DEFAULT NULL,
  `forgotten_password_time` int(11) unsigned DEFAULT NULL,
  `remember_code` varchar(40) DEFAULT NULL,
  `last_login` int(11) unsigned DEFAULT NULL,
  `last_login_ip` varbinary(16) NOT NULL,
  `login_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


--
-- Dumping data for table 'users'
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `salt`, `cms`, `active`, `activation_code`, `forgotten_password_code`, `forgotten_password_time`, `remember_code`, `last_login`, `last_login_ip`, `login_count`) VALUES
(1, 'admin@admin.com', 'administrator', 'd8a03ff18d3cb85de971d57da4f5a207dee479ac', NULL, 2, 1, NULL, NULL, NULL, '35986412fcda34803b95979e019cad9c08dc937a', 1380132531, 0x7f000001, 0);


DROP TABLE IF EXISTS `profiles`;

--
-- Table structure for table 'profiles'
--

CREATE TABLE IF NOT EXISTS `profiles` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `ip_address` varbinary(16) NOT NULL,
  `created_on` int(11) unsigned NOT NULL DEFAULT '0',
  `created_by` mediumint(8) unsigned DEFAULT NULL,
  `edited_on` int(11) unsigned DEFAULT NULL,
  `edited_by` mediumint(8) unsigned DEFAULT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `occupation` varchar(200) DEFAULT NULL,
  `gender` enum('male','female','not_telling') DEFAULT NULL,
  `avatar` varchar(200) DEFAULT NULL,
  `public_email` varchar(100) DEFAULT NULL,
  `skype` varchar(50) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `dob` int(11) DEFAULT NULL,
  `locality` text,
  `comment` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `ip_address`, `created_on`, `created_by`, `edited_on`, `edited_by`, `display_name`, `full_name`, `occupation`, `gender`, `avatar`, `public_email`, `skype`, `website`, `dob`, `locality`, `comment`) VALUES
(1, 1, 0x7f000001, 1268889823, NULL, 1379577394, 1, 'Fröken Bock', 'Fröken Hildur Bock', NULL, 'female', NULL, NULL, 'Fröken-Bock', NULL, NULL, '', '');


DROP TABLE IF EXISTS `users_groups`;

--
-- Table structure for table 'users_groups'
--

CREATE TABLE `users_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_users_groups_users1_idx` (`user_id`),
  KEY `fk_users_groups_groups1_idx` (`group_id`),
  CONSTRAINT `uc_users_groups` UNIQUE (`user_id`, `group_id`),
  CONSTRAINT `fk_users_groups_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_groups_groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES
(1,1,1),
(2,1,3);


DROP TABLE IF EXISTS `login_attempts`;

--
-- Table structure for table 'login_attempts'
--

CREATE TABLE `login_attempts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varbinary(16) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
