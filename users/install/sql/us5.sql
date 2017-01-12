-- phpMyAdmin SQL Dump
-- version 4.5.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 11, 2017 at 04:28 PM
-- Server version: 5.7.11
-- PHP Version: 5.6.19

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
-- Stand-in structure for view `groups_groups`
--
CREATE TABLE `groups_groups` (
`id` bigint(20)
,`parent_id` int(11)
,`child_id` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `us_field_defs`
--

CREATE TABLE `us_field_defs` (
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
  `regex_display` varchar(500) COLLATE utf8_bin DEFAULT NULL 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `us_field_defs`
--

INSERT INTO `us_field_defs` (`id`, `name`, `alias`, `display_lang`, `min`, `max`, `required`, `unique_in_table`, `match_field`, `is_numeric`, `valid_email`, `regex`, `regex_display`) VALUES
(1, 'users.username', 'username', 'USERNAME', 1, 150, 1, 'users', NULL, NULL, NULL, '/^[^\\t !@#$%^&*(){}\\[\\]`~\\\\|]*$/', 'No spaces or special characters'),
(2, 'users.fname', 'fname', 'FNAME', 1, 150, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'users.lname', 'lname', 'LNAME', 1, 150, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'users.email', 'email', 'EMAIL', 3, 250, 1, 'users', NULL, NULL, 1, NULL, NULL),
(5, 'users.password', 'password', 'PASSWORD', 6, 150, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'confirm', NULL, 'CONFIRM_PASSWD', NULL, NULL, 1, NULL, 'password', NULL, NULL, NULL, NULL),
(7, 'users.bio', 'bio', 'BIO_LABEL', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'groups.name', 'name', 'GROUP_NAME', 1, 150, 1, 'groups', NULL, NULL, NULL, NULL, NULL),
(9, 'groups.short_name', 'short_name', 'GROUP_SHORT_NAME', 1, 25, NULL, 'groups', NULL, NULL, NULL, NULL, NULL),
(10, 'grouptypes.name', 'name', 'GROUPTYPE_NAME', 1, 150, 1, 'grouptypes', NULL, NULL, NULL, NULL, NULL),
(11, 'grouptypes.short_name', 'short_name', 'GROUPTYPE_SHORT_NAME', 1, 25, NULL, 'grouptypes', NULL, NULL, NULL, NULL, NULL),
(13, 'groups.grouptype_id', 'grouptype_id', 'GROUPTYPE_LABEL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `us_groups`
--

CREATE TABLE `us_groups` (
  `id` int(11) NOT NULL,
  `grouptype_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `short_name` varchar(25) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL,
  `is_role` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `us_groups`
--

INSERT INTO `us_groups` (`id`, `grouptype_id`, `name`, `short_name`, `admin`, `is_role`) VALUES
(1, 0, 'Users', '', 0, 0),
(2, 0, 'Administrators', '', 1, 0),
(54, 8, 'President', 'Pres', 1, 1),
(55, 8, 'Vice President', 'VP', 0, 1),
(56, 8, 'Acme Multi-National Corp International Division', 'Acme', 0, 0),
(57, 11, 'Zoological Department', 'ZD', 0, 0),
(58, 11, 'Department Head', 'DH', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `us_groups_menus`
--

CREATE TABLE `us_groups_menus` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `us_groups_menus`
--

INSERT INTO `us_groups_menus` (`id`, `group_id`, `menu_id`) VALUES
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
-- Table structure for table `us_groups_pages`
--

CREATE TABLE `us_groups_pages` (
  `id` int(11) NOT NULL,
  `allow_deny` char(1) NOT NULL DEFAULT 'A',
  `group_id` int(15) DEFAULT NULL,
  `grouprole_id` int(11) DEFAULT NULL,
  `page_id` int(15) NOT NULL,
  `auth` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `us_groups_pages`
--

INSERT INTO `us_groups_pages` (`id`, `allow_deny`, `group_id`, `grouprole_id`, `page_id`, `auth`) VALUES
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
(53, 'A', 57, NULL, 93, ''),
(60, 'A', 56, NULL, 93, '');

-- --------------------------------------------------------

--
-- Table structure for table `us_groups_roles_users`
--

CREATE TABLE `us_groups_roles_users` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL COMMENT AS `null = all groups`,
  `role_group_id` int(11) DEFAULT NULL COMMENT AS `null = all roles`,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `us_groups_roles_users`
--

INSERT INTO `us_groups_roles_users` (`id`, `group_id`, `role_group_id`, `user_id`) VALUES
(6, 41, 8, 4),
(7, 43, 14, 1),
(8, 43, 14, 2),
(9, 43, 23, 5),
(10, 39, 8, 2),
(11, 52, 10, 3),
(12, 56, 54, 2),
(13, 57, 58, 2);

-- --------------------------------------------------------

--
-- Stand-in structure for view `us_groups_users`
--
CREATE TABLE `us_groups_users` (
`id` bigint(20)
,`user_id` int(11)
,`group_id` int(11)
,`nested` bigint(20)
);

-- --------------------------------------------------------

--
-- Table structure for table `us_groups_users_raw`
--

CREATE TABLE `us_groups_users_raw` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_is_group` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `us_groups_users_raw`
--

INSERT INTO `us_groups_users_raw` (`id`, `group_id`, `user_id`, `user_is_group`) VALUES
(100, 1, 1, 0),
(102, 1, 2, 0),
(101, 2, 1, 0),
(160, 54, 2, 0),
(159, 56, 2, 0),
(161, 57, 2, 0),
(162, 58, 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `us_grouptypes`
--

CREATE TABLE `us_grouptypes` (
  `id` int(11) NOT NULL,
  `name` varchar(150) CHARACTER SET utf8 NOT NULL,
  `short_name` varchar(15) CHARACTER SET utf8 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `us_grouptypes`
--

INSERT INTO `us_grouptypes` (`id`, `name`, `short_name`) VALUES
(8, 'International Division', 'ID'),
(9, 'Region', 'Region'),
(10, 'Team', 'Team'),
(11, 'Department', 'Dept');

-- --------------------------------------------------------

--
-- Table structure for table `us_menus`
--

CREATE TABLE `us_menus` (
  `id` int(10) NOT NULL,
  `menu_title` varchar(255) NOT NULL,
  `parent` int(10) NOT NULL,
  `dropdown` int(1) NOT NULL,
  `logged_in` int(1) NOT NULL,
  `display_order` int(10) NOT NULL,
  `label` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `link_args` varchar(500) NOT NULL DEFAULT '',
  `page_id` int(11) DEFAULT NULL,
  `icon_class` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `us_menus`
--

INSERT INTO `us_menus` (`id`, `menu_title`, `parent`, `dropdown`, `logged_in`, `display_order`, `label`, `link`, `link_args`, `page_id`, `icon_class`) VALUES
(1, 'main', -1, 0, 1, 0, 'Home', '', '', NULL, 'fa fa-fw fa-home'),
(2, 'main', -1, 0, 1, 2, 'Dashboard', '', '', 4, 'fa fa-fw fa-cogs'),
(3, 'main', -1, 1, 1, 1, '{{username}}', '', '', NULL, 'fa fa-fw fa-user'),
(4, 'main', 3, 0, 1, 1, 'Profile', '', '', 22, 'fa fa-fw fa-home'),
(5, 'main', 3, 0, 1, 1, 'Logout', '', '', 21, 'fa fa-fw fa-home'),
(6, 'main', -1, 1, 1, 3, 'Help', '', '', NULL, 'fa fa-fw fa-life-ring'),
(8, 'main', -1, 0, 0, 1, 'Register', '', '', 18, 'fa fa-fw fa-plus-square'),
(9, 'main', -1, 0, 0, 2, 'Log In', '', '', 20, 'fa fa-fw fa-sign-in'),
(10, 'admin', -1, 0, 1, 10, 'Info', '', '', 4, ''),
(11, 'admin', -1, 0, 1, 20, 'Settings', '', '', 32, ''),
(14, 'admin', -1, 0, 1, 30, 'Users', '', '', 40, ''),
(16, 'admin', -1, 0, 1, 50, 'Pages', '', '', 6, ''),
(17, 'admin', 20, 0, 1, 10, 'Settings', '', '', 30, ''),
(18, 'admin', -1, 0, 1, 60, 'Menus', '', '', 43, ''),
(20, 'admin', -1, 1, 1, 70, 'Email', '', '', NULL, ''),
(21, 'admin', 20, 0, 1, 20, 'Email Verify Template', '', '?type=verify', 46, ''),
(22, 'admin', 20, 0, 1, 30, 'Forgot Password Template', '', '?type=forgot', 46, ''),
(23, 'main', 6, 0, 0, 99999, 'Verify Resend', '', '', 26, ''),
(26, 'admin', -1, 0, 1, 80, 'Add User(s)', '', '', 59, ''),
(27, 'admin', 20, 0, 1, 40, 'Test', '', '', 33, ''),
(28, 'admin', -1, 1, 1, 90, 'System', '', '', NULL, ''),
(29, 'admin', 28, 0, 1, 10, 'Updates', '', '', 66, ''),
(30, 'admin', 28, 0, 1, 20, 'Backup', '', '', 68, ''),
(31, 'admin', 28, 0, 1, 30, 'Restore', '', '', 69, ''),
(32, 'admin', 28, 0, 1, 40, 'Status', '', '', 70, ''),
(33, 'admin', 28, 0, 1, 50, 'PHP Info', '', '', 71, ''),
(38, 'admin', -1, 1, 1, 40, 'Groups', '', '', NULL, ''),
(39, 'admin', 38, 0, 1, 10, 'Groups', '', '', 74, ''),
(40, 'admin', 38, 0, 1, 20, 'Group Roles', '', '', 84, ''),
(41, 'admin', 38, 0, 1, 30, 'Group Types', '', '', 85, '');

-- --------------------------------------------------------

--
-- Table structure for table `us_pages`
--

CREATE TABLE `us_pages` (
  `id` int(11) NOT NULL,
  `page` varchar(100) NOT NULL,
  `private` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `us_pages`
--

INSERT INTO `us_pages` (`id`, `page`, `private`) VALUES
(6, '/UserSpice5-Dev/users/admin_pages.php', 1),
(14, '/UserSpice5-Dev/users/forgot_password.php', 0),
(15, '/UserSpice5-Dev/users/password_reset.php', 0),
(18, '/UserSpice5-Dev/users/join.php', 0),
(20, '/UserSpice5-Dev/users/login.php', 0),
(21, '/UserSpice5-Dev/users/logout.php', 0),
(22, '/UserSpice5-Dev/users/profile.php', 1),
(26, '/UserSpice5-Dev/users/verify_resend.php', 0),
(30, '/UserSpice5-Dev/users/admin_email.php', 1),
(32, '/UserSpice5-Dev/users/admin_settings.php', 1),
(33, '/UserSpice5-Dev/users/admin_email_test.php', 1),
(36, '/UserSpice5-Dev/users/admin_page.php', 1),
(39, '/UserSpice5-Dev/users/admin_user.php', 1),
(40, '/UserSpice5-Dev/users/admin_users.php', 1),
(43, '/UserSpice5-Dev/users/admin_menus.php', 0),
(44, '/UserSpice5-Dev/users/admin_menu.php', 0),
(45, '/UserSpice5-Dev/users/admin_menu_item.php', 0),
(46, '/UserSpice5-Dev/users/admin_email_template.php', 1),
(48, '/UserSpice5-Dev/users/index.php', 0),
(49, '/UserSpice5-Dev/users/contact.php', 0),
(50, '/UserSpice5-Dev/users/gallery.php', 1),
(59, '/UserSpice5-Dev/users/admin_users_add.php', 1),
(62, '/UserSpice5-Dev/users/blocked.php', 0),
(66, '/UserSpice5-Dev/users/admin_updates.php', 1),
(68, '/UserSpice5-Dev/users/admin_backup.php', 1),
(69, '/UserSpice5-Dev/users/admin_restore.php', 1),
(70, '/UserSpice5-Dev/users/admin_status.php', 1),
(71, '/UserSpice5-Dev/users/admin_phpinfo.php', 1),
(74, '/UserSpice5-Dev/users/admin_groups.php', 1),
(80, '/UserSpice5-Dev/users/admin_group.php', 1),
(82, '/UserSpice5-Dev/users/oauth_denied.php', 0),
(83, '/UserSpice5-Dev/users/admin_role.php', 1),
(84, '/UserSpice5-Dev/users/admin_roles.php', 1),
(85, '/UserSpice5-Dev/users/admin_grouptypes.php', 1),
(86, '/UserSpice5-Dev/users/admin_grouptype.php', 1),
(87, '/UserSpice5-Dev/users/nologin.php', 0),
(93, '/UserSpice5-Dev/users/admin.php', 1),
(94, '/UserSpice5-Dev/users/admin_general.php', 0),
(95, '/UserSpice5-Dev/users/verify.php', 0),
(96, '/UserSpice5-Dev/users/admin_pages_old.php', 0),
(97, '/UserSpice5-Dev/users/admin_page_old.php', 0);

-- --------------------------------------------------------

--
-- Table structure for table `us_profiles`
--

CREATE TABLE `us_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bio` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `us_settings`
--

CREATE TABLE `us_settings` (
  `id` int(50) NOT NULL,
  `site_name` varchar(100) NOT NULL,
  `site_url` varchar(255) NOT NULL,
  `install_location` varchar(255) NOT NULL,
  `copyright_message` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `site_language` varchar(255) NOT NULL,
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
-- Dumping data for table `us_settings`
--

INSERT INTO `us_settings` (`id`, `site_name`, `site_url`, `install_location`, `copyright_message`, `version`, `site_language`, `site_offline`, `debug_mode`, `query_count`, `track_guest`, `recaptcha`, `force_ssl`, `css_sample`, `css1`, `css2`, `css3`, `mail_method`, `smtp_server`, `smtp_port`, `smtp_transport`, `email_login`, `email_pass`, `from_name`, `from_email`, `email_act`, `recaptcha_private`, `recaptcha_public`, `email_verify_template`, `forgot_password_template`, `redirect_login`, `redirect_logout`, `redirect_deny_nologin`, `redirect_deny_noperm`, `redirect_referrer_login`, `session_timeout`, `allow_remember_me`, `backup_dest`, `agreement`, `glogin`, `fblogin`, `gid`, `gsecret`, `fbid`, `fbsecret`, `gcallback`, `fbcallback`, `allow_username_change`) VALUES
(1, 'UserSpice5', 'http://localhost/UserSpice5-Dev/', '', 'US', '5.0.0a', 'english.php', 0, 1, 1, 1, 0, 0, 1, 'us_core/css/color_schemes/standard.css', 'us_core/css/blank.css', 'us_core/css/blank.css', 'smtp', '', 25, 'TLS', '', '', 'UserSpice Admin', '', 0, '', '', '&lt;p&gt;Congratulations {{fname}},&lt;/p&gt;\n&lt;p&gt;Thanks for signing up Please click the link below to verify your email address.&lt;/p&gt;\n&lt;p&gt;{{url}}&lt;/p&gt;\n&lt;p&gt;Once you verify your email address you will be ready to login!&lt;/p&gt;\n&lt;p&gt;Sincerely,&lt;/p&gt;\n&lt;p&gt;-The {{sitename}} Team-&lt;/p&gt;', '&lt;p&gt;Hello {{fname}},&lt;/p&gt;\n&lt;p&gt;You are receiving this email because a request was made to reset your password. If this was not you, you may disgard this email.&lt;/p&gt;\n&lt;p&gt;If this was you, click the link below to continue with the password reset process.&lt;/p&gt;\n&lt;p&gt;{{url}}&lt;/p&gt;\n&lt;p&gt;Sincerely,&lt;/p&gt;\n&lt;p&gt;-The {{sitename}} Team-&lt;/p&gt;', 'profile.php', 'index.php', 'login.php', 'index.php', 1, 86400, 1, 'backup_userspice/', 'Welcome to our website. If you continue to browse and use this website, you are agreeing to comply with and be bound by the following terms and conditions of use, which together with our privacy policy govern our relationship with you in relation to this website. If you disagree with any part of these terms and conditions, please do not use our website.\r\n\r\nThe use of this website is subject to the following terms of use:\r\n\r\nThe content of the pages of this website is for your general information and use only. It is subject to change without notice.\r\n\r\nThis website uses cookies to monitor browsing preferences. If you do allow cookies to be used, the following personal information may be stored by us for use by third parties.\r\n\r\nNeither we nor any third parties provide any warranty or guarantee as to the accuracy, timeliness, performance, completeness or suitability of the information and materials found or offered on this website for any particular purpose.\r\n\r\nYou acknowledge that such information and materials may contain inaccuracies or errors and we expressly exclude liability for any such inaccuracies or errors to the fullest extent permitted by law.\r\n\r\nYour use of any information or materials on this website is entirely at your own risk, for which we shall not be liable. It shall be your own responsibility to ensure that any products, services or information available through this website meet your specific requirements.\r\n\r\nThis website contains material which is owned by or licensed to us. This material includes, but is not limited to, the design, layout, look, appearance and graphics. Reproduction is prohibited other than in accordance with the copyright notice, which forms part of these terms and conditions.\r\nAll trade marks reproduced in this website which are not the property of, or licensed to, the operator are acknowledged on the website.\r\n\r\nUnauthorised use of this website may give rise to a claim for damages and/or be a criminal offence.\r\n\r\nFrom time to time this website may also include links to other websites. These links are provided for your convenience to provide further information. They do not signify that we endorse the website(s). We have no responsibility for the content of the linked website(s).', 0, 0, '', '', '', '', 'https://us.raysee.net/users/helpers/gcallback.php', 'https://us.raysee.net/users/helpers/fbcallback.php', 1);

-- --------------------------------------------------------

--
-- Table structure for table `us_users`
--

CREATE TABLE `us_users` (
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
-- Dumping data for table `us_users`
--

INSERT INTO `us_users` (`id`, `email`, `username`, `password`, `fname`, `lname`, `permissions`, `logins`, `account_owner`, `account_id`, `company`, `stripe_cust_id`, `billing_phone`, `billing_srt1`, `billing_srt2`, `billing_city`, `billing_state`, `billing_zip_code`, `timezone_string`, `join_date`, `last_login`, `email_verified`, `vericode`, `title`, `active`, `bio`, `google_uid`, `facebook_uid`) VALUES
(1, 'userspicephp@gmail.com', 'admin', '$2y$12$iE87plmPoyV1rjoZPZENLOi55frC3HrQAz70VI/ud.mzbco2wz/1S', 'Admin', 'User', 1, 319, 1, 0, 'UserSpice', '', '', '', '', '', '', '', 'America/Toronto', '2016-01-01 00:00:00', '2016-12-31 13:40:06', 1, '322418', '', 0, '&lt;p&gt;This is the admin user default bio&lt;/p&gt;', '', ''),
(2, 'noreply@userspice.com', 'user', '$2y$12$HZa0/d7evKvuHO8I3U8Ff.pOjJqsGTZqlX8qURratzP./EvWetbkK', 'user2', 'user', 1, 18, 1, 0, 'none', '', '', '', '', '', '', '', 'Europe/Tirane', '2016-01-02 00:00:00', '2016-10-27 07:15:39', 1, '970748', '', 1, '&lt;p&gt;This is the user user bio&lt;/p&gt;', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `us_users_online`
--

CREATE TABLE `us_users_online` (
  `id` int(10) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `timestamp` varchar(15) NOT NULL,
  `user_id` int(10) NOT NULL,
  `session` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `us_users_online`
--

INSERT INTO `us_users_online` (`id`, `ip`, `timestamp`, `user_id`, `session`) VALUES
(2, '::1', '1483259458', 0, ''),
(5, '::1', '1483652955', 1, ''),
(6, '::1', '1480108084', 2, ''),
(7, '::1', '1483257100', 0, ''),
(8, '::1', '1483257100', 0, ''),
(9, '::1', '1483257110', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `us_users_session`
--

CREATE TABLE `us_users_session` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `uagent` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `us_users_session`
--

INSERT INTO `us_users_session` (`id`, `user_id`, `hash`, `uagent`) VALUES
(1, 1, '5f2e2069f4413fa356e94c26b7199092cd1ab40ff22e233861ad8ce23b4e54ff', 'Mozilla (Windows NT 10.0; WOW64; rv:50.0) Gecko Firefox'),
(2, 1, '444c0fe386ec5152d3941b558589b069429e34371e15a684a6929d44e067a422', 'Mozilla (Windows NT 10.0; WOW64; rv:50.0) Gecko Firefox');

-- --------------------------------------------------------

--
-- Structure for view `groups_groups`
--
DROP TABLE IF EXISTS `groups_groups`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `groups_groups`  AS  select ((`groups`.`id` * 10000) + `groups`.`id`) AS `id`,`groups`.`id` AS `parent_id`,`groups`.`id` AS `child_id` from `us_groups` `groups` union select ((`ug1`.`group_id` * 10000) + `ug1`.`user_id`) AS `id`,`ug1`.`group_id` AS `parent_id`,`ug1`.`user_id` AS `child_id` from `us_groups_users_raw` `ug1` where (`ug1`.`user_is_group` = 1) union select ((`ug1`.`group_id` * 10000) + `ug2`.`user_id`) AS `id`,`ug1`.`group_id` AS `parent_id`,`ug2`.`user_id` AS `child_id` from (`us_groups_users_raw` `ug1` join `us_groups_users_raw` `ug2` on((`ug1`.`user_id` = `ug2`.`group_id`))) where ((`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 1)) union select ((`ug1`.`group_id` * 10000) + `ug3`.`user_id`) AS `id`,`ug1`.`group_id` AS `group_id`,`ug3`.`user_id` AS `user_id` from ((`us_groups_users_raw` `ug1` join `us_groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `us_groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) where ((`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 1)) union select ((`ug1`.`group_id` * 10000) + `ug4`.`user_id`) AS `id`,`ug1`.`group_id` AS `group_id`,`ug4`.`user_id` AS `user_id` from (((`us_groups_users_raw` `ug1` join `us_groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `us_groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) join `us_groups_users_raw` `ug4` on((`ug3`.`group_id` = `ug4`.`user_id`))) where ((`ug4`.`user_is_group` = 1) and (`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 1)) ;

-- --------------------------------------------------------

--
-- Structure for view `us_groups_users`
--
DROP TABLE IF EXISTS `us_groups_users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `us_groups_users`  AS  select `us_groups_users_raw`.`id` AS `id`,`us_groups_users_raw`.`user_id` AS `user_id`,`us_groups_users_raw`.`group_id` AS `group_id`,0 AS `nested` from `us_groups_users_raw` where (`us_groups_users_raw`.`user_is_group` = 0) union select (`ug1`.`user_id` + (`ug2`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug2`.`group_id` AS `group_id`,1 AS `nested` from (`us_groups_users_raw` `ug1` join `us_groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) where ((`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) union select (`ug1`.`user_id` + (`ug3`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug3`.`group_id` AS `group_id`,1 AS `nested` from ((`us_groups_users_raw` `ug1` join `us_groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `us_groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) where ((`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) union select (`ug1`.`user_id` + (`ug4`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug4`.`group_id` AS `group_id`,1 AS `nested` from (((`us_groups_users_raw` `ug1` join `us_groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `us_groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) join `us_groups_users_raw` `ug4` on((`ug3`.`group_id` = `ug4`.`user_id`))) where ((`ug4`.`user_is_group` = 1) and (`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) union select (`ug1`.`user_id` + (`ug5`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug5`.`group_id` AS `group_id`,1 AS `nested` from ((((`us_groups_users_raw` `ug1` join `us_groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `us_groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) join `us_groups_users_raw` `ug4` on((`ug3`.`group_id` = `ug4`.`user_id`))) join `us_groups_users_raw` `ug5` on((`ug4`.`group_id` = `ug5`.`user_id`))) where ((`ug5`.`user_is_group` = 1) and (`ug4`.`user_is_group` = 1) and (`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `us_field_defs`
--
ALTER TABLE `us_field_defs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `us_groups`
--
ALTER TABLE `us_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `us_groups_menus`
--
ALTER TABLE `us_groups_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `us_groups_pages`
--
ALTER TABLE `us_groups_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_id_2` (`group_id`,`page_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `page_id_2` (`page_id`),
  ADD KEY `auth` (`auth`);

--
-- Indexes for table `us_groups_roles_users`
--
ALTER TABLE `us_groups_roles_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `role_id` (`role_group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `us_groups_users_raw`
--
ALTER TABLE `us_groups_users_raw`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_id_2` (`group_id`,`user_id`,`user_is_group`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_is_group` (`user_is_group`);

--
-- Indexes for table `us_grouptypes`
--
ALTER TABLE `us_grouptypes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `us_menus`
--
ALTER TABLE `us_menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `us_pages`
--
ALTER TABLE `us_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `us_profiles`
--
ALTER TABLE `us_profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `us_settings`
--
ALTER TABLE `us_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `us_users`
--
ALTER TABLE `us_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `EMAIL` (`email`) USING BTREE;

--
-- Indexes for table `us_users_online`
--
ALTER TABLE `us_users_online`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `us_users_session`
--
ALTER TABLE `us_users_session`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `us_field_defs`
--
ALTER TABLE `us_field_defs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `us_groups`
--
ALTER TABLE `us_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;
--
-- AUTO_INCREMENT for table `us_groups_menus`
--
ALTER TABLE `us_groups_menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
--
-- AUTO_INCREMENT for table `us_groups_pages`
--
ALTER TABLE `us_groups_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;
--
-- AUTO_INCREMENT for table `us_groups_roles_users`
--
ALTER TABLE `us_groups_roles_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `us_groups_users_raw`
--
ALTER TABLE `us_groups_users_raw`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;
--
-- AUTO_INCREMENT for table `us_grouptypes`
--
ALTER TABLE `us_grouptypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT for table `us_menus`
--
ALTER TABLE `us_menus`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
--
-- AUTO_INCREMENT for table `us_pages`
--
ALTER TABLE `us_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;
--
-- AUTO_INCREMENT for table `us_profiles`
--
ALTER TABLE `us_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `us_settings`
--
ALTER TABLE `us_settings`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `us_users`
--
ALTER TABLE `us_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `us_users_online`
--
ALTER TABLE `us_users_online`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `us_users_session`
--
ALTER TABLE `us_users_session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
