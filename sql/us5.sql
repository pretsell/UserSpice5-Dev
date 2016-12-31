-- phpMyAdmin SQL Dump
-- version 4.5.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2016 at 10:43 PM
-- Server version: 5.7.11
-- PHP Version: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `us5`
--

-- --------------------------------------------------------

--
-- Table structure for table `field_defs`
--

CREATE TABLE `field_defs` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  `alias` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `display_lang` varchar(50) COLLATE utf8_bin NOT NULL,
  `min` int(11) DEFAULT NULL,
  `max` int(11) DEFAULT NULL,
  `required` tinyint(1) DEFAULT NULL,
  `unique_in_table` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `match_field` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `is_numeric` tinyint(1) DEFAULT NULL,
  `valid_email` tinyint(1) DEFAULT NULL,
  `regex` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `regex_display` varchar(500) COLLATE utf8_bin DEFAULT NULL COMMENT AS `This will enable a non-tech human readable display if someone asks for a regex match validation`
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `field_defs`
--

INSERT INTO `field_defs` (`id`, `name`, `alias`, `display_lang`, `min`, `max`, `required`, `unique_in_table`, `match_field`, `is_numeric`, `valid_email`, `regex`, `regex_display`) VALUES
(1, 'users.username', 'username', 'USERNAME', 1, 150, 1, 'users', NULL, NULL, NULL, '/^[^\\t !@#$%^&*(){}\\[\\]`~\\\\|]*$/', 'No spaces or special characters'),
(2, 'users.fname', 'fname', 'FNAME', 1, 150, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'users.lname', 'lname', 'LNAME', 1, 150, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'users.email', 'email', 'EMAIL', 3, 250, 1, 'users', NULL, NULL, 1, NULL, NULL),
(5, 'users.password', 'password', 'PASSWORD', 6, 150, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'confirm', NULL, 'CONFIRM_PASSWD', NULL, NULL, 1, NULL, 'password', NULL, NULL, NULL, NULL),
(7, 'users.bio', 'bio', 'BIO_LABEL', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'groups.name', 'name', 'GROUP_NAME_LABEL', 1, 150, 1, 'groups', NULL, NULL, NULL, NULL, NULL),
(9, 'groups.short_name', 'short_name', 'GROUP_SHORT_NAME_LABEL', 1, 25, NULL, 'groups', NULL, NULL, NULL, NULL, NULL),
(10, 'grouptypes.name', 'name', 'GROUPTYPE_NAME_LABEL', 1, 150, 1, 'grouptypes', NULL, NULL, NULL, NULL, NULL),
(11, 'grouptypes.short_name', 'short_name', 'GROUPTYPE_SHORT_NAME_LABEL', 1, 25, NULL, 'grouptypes', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `grouptype_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `short_name` varchar(25) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL,
  `is_role` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `grouptype_id`, `name`, `short_name`, `admin`, `is_role`) VALUES
(1, NULL, 'User', NULL, 0, 0),
(2, NULL, 'Administrator', NULL, 1, 0),
(3, NULL, 'Foo2', NULL, 1, 0),
(4, 9, 'Silk Road AG', '', 0, 0),
(5, NULL, 'Foo1', '', 0, 0),
(6, NULL, 'Foo3', NULL, 0, 0),
(7, NULL, 'newgroupb', NULL, 0, 0),
(8, 10, 'Focus Group Leader', 'FL', 0, 1),
(9, 3, 'Focus Group Women\'s Ministry Coordinator', 'FGWMC', 0, 1),
(10, 9, 'Affinity Group Director', 'AD', 0, 1),
(11, 9, 'Affinity Group Women&#039;s Ministry Coordinator', 'AGWMC', 0, 1),
(12, 11, 'Team Leader', 'TL', 0, 1),
(13, 4, 'Team Women\'s Ministry Coordinator', 'Team WMC', 0, 1),
(14, 8, 'Church Planting Division Director', 'CPD', 0, 1),
(15, 1, 'Women\'s Ministry Director (CPD)', 'WMD', 0, 1),
(16, 3, 'Assistant Focus Group Leader', 'AFL', 0, 1),
(17, 3, 'Assistant Focus Group Women\'s Ministry Coordinator', 'FG AWMC', 0, 1),
(18, 2, 'Assistant Affinity Group Director', 'AAD', 0, 1),
(19, 2, 'Assistant Affinity Group Women\'s Ministry Coordinator', 'AG AWMC', 0, 1),
(20, 4, 'Assistant Team Leader', 'ATL', 0, 1),
(21, 4, 'Assistant Team Women\'s Ministry Coordinator', 'Team AWMC', 0, 1),
(22, 1, 'Assistant Church Planting Division Director', 'ACPD', 0, 1),
(23, 1, 'Assistant Women\'s Ministry Director', 'AWMD', 0, 1),
(39, 10, 'Balkans FG', 'BFG', 0, 0),
(40, 11, 'Roma CP', 'RCP', 0, 0),
(42, NULL, 'foo8', '', 0, 0),
(43, 8, 'Charlie!', 'Charles!', 0, 0);

-- --------------------------------------------------------

--
-- Stand-in structure for view `groups_groups`
--
CREATE TABLE `groups_groups` (
`id*10000+id` bigint(20)
,`parent_id` int(11)
,`child_id` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `groups_menus`
--

CREATE TABLE `groups_menus` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `groups_menus`
--

INSERT INTO `groups_menus` (`id`, `group_id`, `menu_id`) VALUES
(22, 2, 37),
(20, 1, 4),
(19, 0, 4),
(14, 0, 5),
(15, 0, 3),
(16, 0, 1),
(17, 0, 6),
(18, 2, 2),
(31, 2, 38),
(27, 2, 39),
(30, 2, 40),
(29, 2, 41),
(32, 2, 18),
(33, 2, 16),
(34, 2, 12),
(35, 2, 13),
(36, 2, 24),
(37, 2, 25),
(38, 2, 14),
(39, 2, 20),
(40, 2, 17),
(41, 2, 27),
(42, 2, 26),
(43, 2, 33);

-- --------------------------------------------------------

--
-- Table structure for table `groups_pages`
--

CREATE TABLE `groups_pages` (
  `id` int(11) NOT NULL,
  `allow_deny` char(1) NOT NULL DEFAULT 'A',
  `group_id` int(15) DEFAULT NULL,
  `grouprole_id` int(11) DEFAULT NULL,
  `page_id` int(15) NOT NULL,
  `auth` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups_pages`
--

INSERT INTO `groups_pages` (`id`, `allow_deny`, `group_id`, `grouprole_id`, `page_id`, `auth`) VALUES
(2, 'A', 2, NULL, 27, ''),
(3, 'A', 1, NULL, 24, ''),
(4, 'A', 1, NULL, 22, ''),
(5, 'A', 2, NULL, 13, ''),
(6, 'A', 2, NULL, 12, ''),
(7, 'A', 1, NULL, 11, ''),
(8, 'A', 2, NULL, 10, ''),
(9, 'A', 2, NULL, 9, ''),
(10, 'A', 2, NULL, 8, ''),
(11, 'A', 2, NULL, 7, ''),
(12, 'A', 2, NULL, 6, ''),
(13, 'A', 2, NULL, 5, ''),
(14, 'A', 2, NULL, 4, ''),
(15, 'A', 1, NULL, 3, ''),
(21, 'A', 2, NULL, 36, ''),
(22, 'A', 1, NULL, 50, ''),
(23, 'A', 1, NULL, 56, ''),
(26, 'A', 2, NULL, 60, ''),
(27, 'A', 1, NULL, 55, ''),
(28, 'A', 43, NULL, 30, ''),
(29, 'A', 43, NULL, 46, ''),
(30, 'A', 43, NULL, 33, '');

-- --------------------------------------------------------

--
-- Table structure for table `groups_roles_users`
--

CREATE TABLE `groups_roles_users` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL COMMENT AS `null = all groups`,
  `role_group_id` int(11) DEFAULT NULL COMMENT AS `null = all roles`,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `groups_roles_users`
--

INSERT INTO `groups_roles_users` (`id`, `group_id`, `role_group_id`, `user_id`) VALUES
(6, 41, 8, 4),
(7, 43, 14, 1),
(8, 43, 14, 2),
(9, 43, 23, 5),
(10, 39, 8, 2);

-- --------------------------------------------------------

--
-- Stand-in structure for view `groups_users`
--
CREATE TABLE `groups_users` (
`id` bigint(20)
,`user_id` int(11)
,`group_id` int(11)
,`nested` bigint(20)
);

-- --------------------------------------------------------

--
-- Table structure for table `groups_users_raw`
--

CREATE TABLE `groups_users_raw` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_is_group` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups_users_raw`
--

INSERT INTO `groups_users_raw` (`id`, `group_id`, `user_id`, `user_is_group`) VALUES
(100, 1, 1, 0),
(102, 1, 2, 0),
(110, 1, 4, 0),
(111, 1, 5, 0),
(116, 1, 6, 0),
(117, 1, 7, 0),
(101, 2, 1, 0),
(107, 3, 1, 1),
(108, 3, 2, 1),
(109, 3, 4, 1),
(106, 5, 1, 1),
(112, 7, 3, 0),
(114, 7, 5, 0),
(144, 8, 2, 0),
(132, 8, 4, 0),
(140, 14, 1, 0),
(141, 14, 2, 0),
(142, 23, 5, 0),
(143, 39, 2, 0),
(126, 40, 2, 0),
(127, 40, 4, 0),
(133, 43, 1, 0),
(134, 43, 2, 0),
(136, 43, 4, 0),
(137, 43, 5, 0),
(138, 43, 6, 0),
(139, 43, 7, 0);

-- --------------------------------------------------------

--
-- Table structure for table `grouptypes`
--

CREATE TABLE `grouptypes` (
  `id` int(11) NOT NULL,
  `name` varchar(150) COLLATE utf8_bin NOT NULL,
  `short_name` varchar(15) COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `grouptypes`
--

INSERT INTO `grouptypes` (`id`, `name`, `short_name`) VALUES
(8, 'Church Planting Division', 'CPD'),
(9, 'Affinity Group', 'AG'),
(10, 'Focus Group', 'FG'),
(11, 'Team', 'Team');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(10) NOT NULL,
  `menu_title` varchar(255) NOT NULL,
  `parent` int(10) NOT NULL,
  `dropdown` int(1) NOT NULL,
  `logged_in` int(1) NOT NULL,
  `display_order` int(10) NOT NULL,
  `label` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `page_id` int(11) DEFAULT NULL,
  `icon_class` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `menu_title`, `parent`, `dropdown`, `logged_in`, `display_order`, `label`, `link`, `page_id`, `icon_class`) VALUES
(1, 'main', -1, 0, 1, 0, 'Home', '', NULL, 'fa fa-fw fa-home'),
(2, 'main', -1, 0, 1, 2, 'Dashboard', 'users/admin.php', 4, 'fa fa-fw fa-cogs'),
(3, 'main', -1, 1, 1, 1, '{{username}}', '', NULL, 'fa fa-fw fa-user'),
(4, 'main', 3, 0, 1, 1, 'Profile', 'users/profile.php', 22, 'fa fa-fw fa-home'),
(5, 'main', 3, 0, 1, 1, 'Logout', 'users/logout.php', 21, 'fa fa-fw fa-home'),
(6, 'main', -1, 1, 1, 3, 'Help', '', NULL, 'fa fa-fw fa-life-ring'),
(8, 'main', -1, 0, 0, 1, 'Register', 'users/join.php', 18, 'fa fa-fw fa-plus-square'),
(9, 'main', -1, 0, 0, 2, 'Log In', 'users/login.php', 20, 'fa fa-fw fa-sign-in'),
(10, 'admin', -1, 0, 1, 10, 'Info', 'users/admin.php', 4, ''),
(11, 'admin', -1, 1, 1, 20, 'Settings', '', NULL, ''),
(12, 'admin', 11, 0, 1, 10, 'Security', 'users/admin_security.php', 32, ''),
(13, 'admin', 11, 0, 1, 20, 'CSS', 'users/admin_css.php', 29, ''),
(14, 'admin', -1, 0, 1, 30, 'Users', 'users/admin_users.php', 40, ''),
(16, 'admin', -1, 0, 1, 50, 'Pages', 'users/admin_pages.php', 6, ''),
(17, 'admin', 20, 0, 1, 10, 'Settings', 'users/admin_email.php', 30, ''),
(18, 'admin', -1, 0, 1, 60, 'Menus', 'users/admin_menus.php', 43, ''),
(20, 'admin', -1, 1, 1, 70, 'Email', '', NULL, ''),
(21, 'admin', 20, 0, 1, 20, 'Email Verify Template', 'users/admin_email_template.php?type=verify', NULL, ''),
(22, 'admin', 20, 0, 1, 30, 'Forgot Password Template', 'users/admin_email_template.php?type=forgot', NULL, ''),
(23, 'main', 6, 0, 0, 99999, 'Verify Resend', 'users/verify_resend.php', 26, ''),
(24, 'admin', 11, 0, 1, 30, 'General', 'users/admin_general.php', 31, ''),
(25, 'admin', 11, 0, 1, 40, 'Redirects', 'users/admin_redirects.php', 57, ''),
(26, 'admin', -1, 0, 1, 80, 'Add User(s)', 'users/admin_users_add.php', 59, ''),
(27, 'admin', 20, 0, 1, 40, 'Test', 'users/admin_email_test.php', 33, ''),
(28, 'admin', -1, 1, 1, 90, 'System', '', NULL, ''),
(29, 'admin', 28, 0, 1, 10, 'Updates', 'users/admin_updates.php', 66, ''),
(30, 'admin', 28, 0, 1, 20, 'Backup', 'users/admin_backup.php', 68, ''),
(31, 'admin', 28, 0, 1, 30, 'Restore', 'users/admin_restore.php', 69, ''),
(32, 'admin', 28, 0, 1, 40, 'Status', 'users/admin_status.php', 70, ''),
(33, 'admin', 28, 0, 1, 50, 'PHP Info', 'users/admin_phpinfo.php', 71, ''),
(34, 'admin', 11, 0, 1, 50, 'Registration', 'users/admin_registration.php', 73, ''),
(35, 'admin', 11, 0, 1, 60, 'Google Login', 'users/admin_googlelogin.php', 75, ''),
(36, 'admin', 11, 0, 1, 70, 'Facebook Login', 'users/admin_facebooklogin.php', 78, ''),
(38, 'admin', -1, 1, 1, 40, 'Groups', '', NULL, ''),
(39, 'admin', 38, 0, 1, 10, 'Groups', 'users/admin_groups.php', 74, ''),
(40, 'admin', 38, 0, 1, 20, 'Group Roles', 'users/admin_roles.php', NULL, ''),
(41, 'admin', 38, 0, 1, 30, 'Group Types', 'users/admin_grouptypes.php', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `page` varchar(100) NOT NULL,
  `private` int(11) NOT NULL DEFAULT '0',
  `title_lang` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `page`, `private`, `title_lang`) VALUES
(1, 'index.php', 0, NULL),
(2, 'z_us_root.php', 0, NULL),
(4, 'users/admin.php', 1, NULL),
(6, 'users/admin_pages.php', 1, NULL),
(14, 'users/forgot_password.php', 0, NULL),
(15, 'users/password_reset.php', 0, NULL),
(17, 'users/init.php', 0, NULL),
(18, 'users/join.php', 0, NULL),
(20, 'users/login.php', 0, NULL),
(21, 'users/logout.php', 0, NULL),
(22, 'users/profile.php', 1, NULL),
(25, 'users/verify.php', 0, NULL),
(26, 'users/verify_resend.php', 0, NULL),
(28, 'usersc/empty.php', 0, NULL),
(29, 'users/admin_css.php', 1, NULL),
(30, 'users/admin_email.php', 1, NULL),
(31, 'users/admin_general.php', 1, NULL),
(32, 'users/admin_security.php', 1, NULL),
(33, 'users/admin_email_test.php', 1, NULL),
(36, 'users/admin_page.php', 1, NULL),
(39, 'users/admin_user.php', 1, NULL),
(40, 'users/admin_users.php', 1, NULL),
(42, 'users/db_cred.php', 1, NULL),
(43, 'users/admin_menus.php', 0, NULL),
(44, 'users/admin_menu.php', 0, NULL),
(45, 'users/admin_menu_item.php', 0, NULL),
(46, 'users/admin_email_template.php', 1, NULL),
(48, 'users/index.php', 0, NULL),
(49, 'contact.php', 0, NULL),
(50, 'gallery.php', 1, NULL),
(51, 'join.php', 0, NULL),
(52, 'login.php', 0, 'SIGN_IN'),
(55, 'profile.php', 1, NULL),
(57, 'users/admin_redirects.php', 1, NULL),
(59, 'users/admin_users_add.php', 1, NULL),
(60, 'blocked.php', 0, NULL),
(61, 'forgot_password.php', 0, NULL),
(62, 'users/blocked.php', 0, NULL),
(63, 'password_reset.php', 0, NULL),
(64, 'verify.php', 0, NULL),
(65, 'verify_resend.php', 0, NULL),
(66, 'users/admin_updates.php', 1, NULL),
(68, 'users/admin_backup.php', 1, NULL),
(69, 'users/admin_restore.php', 1, NULL),
(70, 'users/admin_status.php', 1, NULL),
(71, 'users/admin_phpinfo.php', 1, NULL),
(73, 'users/admin_registration.php', 1, NULL),
(74, 'users/admin_groups.php', 1, NULL),
(75, 'users/admin_googlelogin.php', 1, NULL),
(78, 'users/admin_facebooklogin.php', 1, NULL),
(79, 'oauth_denied.php', 0, NULL),
(80, 'users/admin_group.php', 1, NULL),
(82, 'users/oauth_denied.php', 0, NULL),
(83, 'users/admin_role.php', 1, NULL),
(84, 'users/admin_roles.php', 1, NULL),
(85, 'admin_grouptypes.php', 1, 'ADMIN_GROUPTYPES_TITLE'),
(86, 'admin_grouptype.php', 1, 'ADMIN_GROUPTYPE_TITLE'),
(87, 'nologin.php', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bio` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `bio`) VALUES
(19, 4, 'This is your bio'),
(20, 5, 'This is your bio'),
(21, 6, 'This is your bio'),
(22, 7, 'This is your bio');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(50) NOT NULL,
  `site_name` varchar(100) NOT NULL,
  `site_url` varchar(255) NOT NULL,
  `install_location` varchar(255) NOT NULL,
  `copyright_message` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `language` varchar(255) NOT NULL,
  `site_offline` int(1) NOT NULL,
  `debug_mode` int(1) NOT NULL,
  `query_count` int(1) NOT NULL,
  `track_guest` int(1) NOT NULL,
  `recaptcha` int(1) NOT NULL DEFAULT '0',
  `force_ssl` int(1) NOT NULL,
  `css_sample` int(1) NOT NULL,
  `css1` varchar(255) NOT NULL,
  `css2` varchar(255) NOT NULL,
  `css3` varchar(255) NOT NULL,
  `mail_method` varchar(255) NOT NULL,
  `smtp_server` varchar(255) NOT NULL,
  `smtp_port` int(10) NOT NULL,
  `smtp_transport` varchar(255) NOT NULL,
  `email_login` varchar(255) NOT NULL,
  `email_pass` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `email_act` int(1) NOT NULL,
  `recaptcha_private` varchar(255) NOT NULL,
  `recaptcha_public` varchar(255) NOT NULL,
  `email_verify_template` longtext NOT NULL,
  `forgot_password_template` longtext NOT NULL,
  `redirect_login` varchar(255) NOT NULL,
  `redirect_logout` varchar(255) NOT NULL,
  `redirect_deny_nologin` varchar(255) NOT NULL,
  `redirect_deny_noperm` varchar(255) NOT NULL,
  `redirect_referrer_login` int(1) NOT NULL,
  `session_timeout` int(10) NOT NULL,
  `allow_remember_me` int(1) NOT NULL,
  `backup_dest` varchar(255) NOT NULL,
  `agreement` longtext NOT NULL,
  `glogin` int(1) NOT NULL,
  `fblogin` int(1) NOT NULL,
  `gid` varchar(255) NOT NULL,
  `gsecret` varchar(255) NOT NULL,
  `fbid` varchar(255) NOT NULL,
  `fbsecret` varchar(255) NOT NULL,
  `gcallback` varchar(255) NOT NULL,
  `fbcallback` varchar(255) NOT NULL,
  `allow_username_change` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `site_name`, `site_url`, `install_location`, `copyright_message`, `version`, `language`, `site_offline`, `debug_mode`, `query_count`, `track_guest`, `recaptcha`, `force_ssl`, `css_sample`, `css1`, `css2`, `css3`, `mail_method`, `smtp_server`, `smtp_port`, `smtp_transport`, `email_login`, `email_pass`, `from_name`, `from_email`, `email_act`, `recaptcha_private`, `recaptcha_public`, `email_verify_template`, `forgot_password_template`, `redirect_login`, `redirect_logout`, `redirect_deny_nologin`, `redirect_deny_noperm`, `redirect_referrer_login`, `session_timeout`, `allow_remember_me`, `backup_dest`, `agreement`, `glogin`, `fblogin`, `gid`, `gsecret`, `fbid`, `fbsecret`, `gcallback`, `fbcallback`, `allow_username_change`) VALUES
(1, 'UserSpice5', 'http://localhost/UserSpice5-Dev/', '', 'US', '5.0.0a', 'en', 0, 1, 1, 1, 0, 0, 1, 'us_core/css/color_schemes/standard.css', 'us_core/css/blank.css', 'us_core/css/blank.css', 'smtp', '', 25, 'TLS', '', '', 'UserSpice Admin', '', 0, '', '', '&lt;p&gt;Congratulations {{fname}},&lt;/p&gt;\n&lt;p&gt;Thanks for signing up Please click the link below to verify your email address.&lt;/p&gt;\n&lt;p&gt;{{url}}&lt;/p&gt;\n&lt;p&gt;Once you verify your email address you will be ready to login!&lt;/p&gt;\n&lt;p&gt;Sincerely,&lt;/p&gt;\n&lt;p&gt;-The {{sitename}} Team-&lt;/p&gt;', '&lt;p&gt;Hello {{fname}},&lt;/p&gt;\n&lt;p&gt;You are receiving this email because a request was made to reset your password. If this was not you, you may disgard this email.&lt;/p&gt;\n&lt;p&gt;If this was you, click the link below to continue with the password reset process.&lt;/p&gt;\n&lt;p&gt;{{url}}&lt;/p&gt;\n&lt;p&gt;Sincerely,&lt;/p&gt;\n&lt;p&gt;-The {{sitename}} Team-&lt;/p&gt;', 'profile.php', 'index.php', 'login.php', 'index.php', 1, 86400, 1, 'backup_userspice/', 'Welcome to our website. If you continue to browse and use this website, you are agreeing to comply with and be bound by the following terms and conditions of use, which together with our privacy policy govern our relationship with you in relation to this website. If you disagree with any part of these terms and conditions, please do not use our website.\r\n\r\nThe use of this website is subject to the following terms of use:\r\n\r\nThe content of the pages of this website is for your general information and use only. It is subject to change without notice.\r\n\r\nThis website uses cookies to monitor browsing preferences. If you do allow cookies to be used, the following personal information may be stored by us for use by third parties.\r\n\r\nNeither we nor any third parties provide any warranty or guarantee as to the accuracy, timeliness, performance, completeness or suitability of the information and materials found or offered on this website for any particular purpose.\r\n\r\nYou acknowledge that such information and materials may contain inaccuracies or errors and we expressly exclude liability for any such inaccuracies or errors to the fullest extent permitted by law.\r\n\r\nYour use of any information or materials on this website is entirely at your own risk, for which we shall not be liable. It shall be your own responsibility to ensure that any products, services or information available through this website meet your specific requirements.\r\n\r\nThis website contains material which is owned by or licensed to us. This material includes, but is not limited to, the design, layout, look, appearance and graphics. Reproduction is prohibited other than in accordance with the copyright notice, which forms part of these terms and conditions.\r\nAll trade marks reproduced in this website which are not the property of, or licensed to, the operator are acknowledged on the website.\r\n\r\nUnauthorised use of this website may give rise to a claim for damages and/or be a criminal offence.\r\n\r\nFrom time to time this website may also include links to other websites. These links are provided for your convenience to provide further information. They do not signify that we endorse the website(s). We have no responsibility for the content of the linked website(s).', 0, 0, '', '', '', '', 'https://us.raysee.net/users/helpers/gcallback.php', 'https://us.raysee.net/users/helpers/fbcallback.php', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(155) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `permissions` int(11) NOT NULL,
  `logins` int(100) NOT NULL,
  `account_owner` tinyint(4) NOT NULL DEFAULT '0',
  `account_id` int(11) NOT NULL DEFAULT '0',
  `company` varchar(255) NOT NULL,
  `stripe_cust_id` varchar(255) NOT NULL,
  `billing_phone` varchar(20) NOT NULL,
  `billing_srt1` varchar(255) NOT NULL,
  `billing_srt2` varchar(255) NOT NULL,
  `billing_city` varchar(255) NOT NULL,
  `billing_state` varchar(255) NOT NULL,
  `billing_zip_code` varchar(255) NOT NULL,
  `timezone_string` varchar(255) NOT NULL,
  `join_date` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  `email_verified` tinyint(4) NOT NULL DEFAULT '0',
  `vericode` varchar(15) NOT NULL,
  `title` varchar(100) NOT NULL,
  `active` int(1) NOT NULL,
  `bio` longtext NOT NULL,
  `google_uid` varchar(255) NOT NULL,
  `facebook_uid` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `fname`, `lname`, `permissions`, `logins`, `account_owner`, `account_id`, `company`, `stripe_cust_id`, `billing_phone`, `billing_srt1`, `billing_srt2`, `billing_city`, `billing_state`, `billing_zip_code`, `timezone_string`, `join_date`, `last_login`, `email_verified`, `vericode`, `title`, `active`, `bio`, `google_uid`, `facebook_uid`) VALUES
(1, 'userspicephp@gmail.com', 'admin', '$2y$12$iE87plmPoyV1rjoZPZENLOi55frC3HrQAz70VI/ud.mzbco2wz/1S', 'Admin', 'User', 1, 310, 1, 0, 'UserSpice', '', '', '', '', '', '', '', 'America/Toronto', '2016-01-01 00:00:00', '2016-11-23 07:05:10', 1, '322418', '', 0, '&lt;p&gt;This is the admin user default bio&lt;/p&gt;', '', ''),
(2, 'noreply@userspice.com', 'user', '$2y$12$HZa0/d7evKvuHO8I3U8Ff.pOjJqsGTZqlX8qURratzP./EvWetbkK', 'user2', 'user', 1, 18, 1, 0, 'none', '', '', '', '', '', '', '', 'Europe/Tirane', '2016-01-02 00:00:00', '2016-10-27 07:15:39', 1, '970748', '', 1, '&lt;p&gt;This is the user user bio&lt;/p&gt;', '', ''),
(3, 'foo@foo.foo', 'foofoo', '$2y$12$29DeeY0IfEcCvLyE2NtYzueTeFas7tfg.rbeDZ4Un6D6G9aB3VuaK', 'foo', 'foo', 1, 0, 1, 0, '', '', '', '', '', '', '', '', '', '2016-10-25 03:17:28', '0000-00-00 00:00:00', 0, '267211', '', 1, '', '', ''),
(4, 'bar@bar.bar', 'barbar', '$2y$12$HZa0/d7evKvuHO8I3U8Ff.pOjJqsGTZqlX8qURratzP./EvWetbkK', 'barbar', 'barbar', 1, 2, 1, 0, '', '', '', '', '', '', '', '', '', '2016-10-25 03:26:22', '2016-11-01 09:39:25', 1, '396850', '', 1, '', '', ''),
(5, 'plbowers@gmail.com', 'myspecialusername', '$2y$12$cy8/M1hUIe5ZbuzQnPx37e5vkdJNZvJkBU6VfqFQS5en..YucTLX6', 'ab', 'bc', 1, 0, 1, 0, '', '', '', '', '', '', '', '', '', '2016-10-25 15:45:23', '0000-00-00 00:00:00', 0, '200634', '', 1, '', '', ''),
(6, 'foo@foo.boo', 'foox', '$2y$12$MT6g9SZtyxRMLKVtBz6dp.5vq7p3W21TVisTmDIrDOA9VdZXawdhi', 'Foox', 'Foox', 1, 0, 1, 0, '', '', '', '', '', '', '', '', '', '2016-11-05 04:44:59', '0000-00-00 00:00:00', 0, '370373', '', 1, '', '', ''),
(7, 'sam@spade.com', 'sam', '$2y$12$odwgXkedVQ2gyZOayJn0.uW24jRFkV7Zn.fXLj848QeBrhTURPRCC', 'Sam', 'Spade', 1, 1, 1, 0, '', '', '', '', '', '', '', '', '', '2016-11-05 10:54:38', '2016-11-05 10:54:48', 1, '828530', '', 1, '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users_online`
--

CREATE TABLE `users_online` (
  `id` int(10) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `timestamp` varchar(15) NOT NULL,
  `user_id` int(10) NOT NULL,
  `session` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_online`
--

INSERT INTO `users_online` (`id`, `ip`, `timestamp`, `user_id`, `session`) VALUES
(2, '::1', '1479654885', 0, ''),
(5, '::1', '1479940864', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `users_session`
--

CREATE TABLE `users_session` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `uagent` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_session`
--

INSERT INTO `users_session` (`id`, `user_id`, `hash`, `uagent`) VALUES
(1, 1, '5f2e2069f4413fa356e94c26b7199092cd1ab40ff22e233861ad8ce23b4e54ff', 'Mozilla (Windows NT 10.0; WOW64; rv:50.0) Gecko Firefox'),
(2, 1, '444c0fe386ec5152d3941b558589b069429e34371e15a684a6929d44e067a422', 'Mozilla (Windows NT 10.0; WOW64; rv:50.0) Gecko Firefox');

-- --------------------------------------------------------

--
-- Structure for view `groups_groups`
--
DROP TABLE IF EXISTS `groups_groups`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `groups_groups`  AS  select ((`groups`.`id` * 10000) + `groups`.`id`) AS `id*10000+id`,`groups`.`id` AS `parent_id`,`groups`.`id` AS `child_id` from `groups` union select ((`ug1`.`group_id` * 10000) + `ug1`.`user_id`) AS `id`,`ug1`.`group_id` AS `parent_id`,`ug1`.`user_id` AS `child_id` from `groups_users_raw` `ug1` where (`ug1`.`user_is_group` = 1) union select ((`ug1`.`group_id` * 10000) + `ug2`.`user_id`) AS `id`,`ug1`.`group_id` AS `parent_id`,`ug2`.`user_id` AS `child_id` from (`groups_users_raw` `ug1` join `groups_users_raw` `ug2` on((`ug1`.`user_id` = `ug2`.`group_id`))) where ((`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 1)) union select ((`ug1`.`group_id` * 10000) + `ug3`.`user_id`) AS `id`,`ug1`.`group_id` AS `group_id`,`ug3`.`user_id` AS `user_id` from ((`groups_users_raw` `ug1` join `groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) where ((`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 1)) union select ((`ug1`.`group_id` * 10000) + `ug4`.`user_id`) AS `id`,`ug1`.`group_id` AS `group_id`,`ug4`.`user_id` AS `user_id` from (((`groups_users_raw` `ug1` join `groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) join `groups_users_raw` `ug4` on((`ug3`.`group_id` = `ug4`.`user_id`))) where ((`ug4`.`user_is_group` = 1) and (`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 1)) ;

-- --------------------------------------------------------

--
-- Structure for view `groups_users`
--
DROP TABLE IF EXISTS `groups_users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `groups_users`  AS  select `groups_users_raw`.`id` AS `id`,`groups_users_raw`.`user_id` AS `user_id`,`groups_users_raw`.`group_id` AS `group_id`,0 AS `nested` from `groups_users_raw` where (`groups_users_raw`.`user_is_group` = 0) union select (`ug1`.`user_id` + (`ug2`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug2`.`group_id` AS `group_id`,1 AS `nested` from (`groups_users_raw` `ug1` join `groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) where ((`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) union select (`ug1`.`user_id` + (`ug3`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug3`.`group_id` AS `group_id`,1 AS `nested` from ((`groups_users_raw` `ug1` join `groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) where ((`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) union select (`ug1`.`user_id` + (`ug4`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug4`.`group_id` AS `group_id`,1 AS `nested` from (((`groups_users_raw` `ug1` join `groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) join `groups_users_raw` `ug4` on((`ug3`.`group_id` = `ug4`.`user_id`))) where ((`ug4`.`user_is_group` = 1) and (`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) union select (`ug1`.`user_id` + (`ug5`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug5`.`group_id` AS `group_id`,1 AS `nested` from ((((`groups_users_raw` `ug1` join `groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) join `groups_users_raw` `ug4` on((`ug3`.`group_id` = `ug4`.`user_id`))) join `groups_users_raw` `ug5` on((`ug4`.`group_id` = `ug5`.`user_id`))) where ((`ug5`.`user_is_group` = 1) and (`ug4`.`user_is_group` = 1) and (`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `field_defs`
--
ALTER TABLE `field_defs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groups_menus`
--
ALTER TABLE `groups_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `groups_pages`
--
ALTER TABLE `groups_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `page_id_2` (`page_id`),
  ADD KEY `auth` (`auth`);

--
-- Indexes for table `groups_roles_users`
--
ALTER TABLE `groups_roles_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `role_id` (`role_group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `groups_users_raw`
--
ALTER TABLE `groups_users_raw`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_id_2` (`group_id`,`user_id`,`user_is_group`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_is_group` (`user_is_group`);

--
-- Indexes for table `grouptypes`
--
ALTER TABLE `grouptypes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `EMAIL` (`email`) USING BTREE;

--
-- Indexes for table `users_online`
--
ALTER TABLE `users_online`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_session`
--
ALTER TABLE `users_session`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `field_defs`
--
ALTER TABLE `field_defs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
--
-- AUTO_INCREMENT for table `groups_menus`
--
ALTER TABLE `groups_menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
--
-- AUTO_INCREMENT for table `groups_pages`
--
ALTER TABLE `groups_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `groups_roles_users`
--
ALTER TABLE `groups_roles_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `groups_users_raw`
--
ALTER TABLE `groups_users_raw`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;
--
-- AUTO_INCREMENT for table `grouptypes`
--
ALTER TABLE `grouptypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;
--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `users_online`
--
ALTER TABLE `users_online`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `users_session`
--
ALTER TABLE `users_session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
