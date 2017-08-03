-- phpMyAdmin SQL Dump
-- version 4.6.2
-- https://www.phpmyadmin.net/
--
-- Host:
-- Generation Time: Oct 04, 2016 at 08:21 AM
-- Server version: 5.6.25-log
-- PHP Version: 7.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `loginrayseenet`
--

--
-- Table structure for table `validate_rules`
--

CREATE TABLE `validate_rules` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  `display` varchar(250) COLLATE utf8_bin NOT NULL,
  `min` int(11) DEFAULT NULL,
  `max` int(11) DEFAULT NULL,
  `required` tinyint(1) DEFAULT NULL,
  `unique_in_table` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `match_field` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `is_numeric` tinyint(1) DEFAULT NULL,
  `valid_email` tinyint(1) DEFAULT NULL,
  `regex` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `regex_display` varchar(500) COLLATE utf8_bin DEFAULT NULL -- This will enable a non-tech human readable display if someone asks for a regex match validation
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `validate_rules`
--

INSERT INTO `validate_rules` (`id`, `name`, `display`, `min`, `max`, `required`, `unique_in_table`, `match_field`, `is_numeric`, `valid_email`, `regex`, `regex_display`) VALUES
(1, 'groupname', 'Group Name', 1, 150, 1, 'groups', NULL, NULL, NULL, NULL, NULL),
(2, 'username', 'Username', 1, 150, 1, 'users', NULL, NULL, NULL, '/^[^\\t !@#$%^&*(){}\\[\\]`~\\\\|]*$/', 'No spaces or special characters'),
(3, 'fname', 'First Name', 1, 150, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'lname', 'Last Name', 1, 150, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'email', 'Email', 3, 250, 1, 'users', NULL, NULL, 1, NULL, NULL),
(6, 'password', 'Password', 6, 150, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'confirm', 'Confirm Password', NULL, NULL, 1, NULL, 'password', NULL, NULL, NULL, NULL),
(8, 'bio', 'Bio', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `validate_rules`
--
ALTER TABLE `validate_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `validate_rules`
--
ALTER TABLE `validate_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  `icon_class` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groups_menus`
--

CREATE TABLE `groups_menus` (
  `id` int(11) NOT NULL,
  `group_id` int(15) NOT NULL,
  `menu_id` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups_menus`
--

INSERT INTO `groups_menus` (`id`, `group_id`, `menu_id`) VALUES
(13, 3, 4),
(12, 1, 4),
(11, 0, 4),
(14, 0, 5),
(15, 0, 3),
(16, 0, 1),
(17, 0, 6),
(18, 2, 2);

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `menu_title`, `parent`, `dropdown`, `logged_in`, `display_order`, `label`, `link`, `icon_class`) VALUES
(1, 'main', -1, 0, 1, 0, 'Home', '', 'fa fa-fw fa-home'),
(2, 'main', -1, 0, 1, 2, 'Dashboard', 'users/admin.php', 'fa fa-fw fa-cogs'),
(3, 'main', -1, 1, 1, 1, '{{username}}', '', 'fa fa-fw fa-user'),
(4, 'main', 3, 0, 1, 1, 'Profile', 'users/profile.php', 'fa fa-fw fa-home'),
(5, 'main', 3, 0, 1, 1, 'Logout', 'users/logout.php', 'fa fa-fw fa-home'),
(6, 'main', -1, 1, 1, 3, 'Help', '', 'fa fa-fw fa-life-ring'),
(8, 'main', -1, 0, 0, 1, 'Register', 'users/join.php', 'fa fa-fw fa-plus-square'),
(9, 'main', -1, 0, 0, 2, 'Log In', 'users/login.php', 'fa fa-fw fa-sign-in'),
(10, 'admin', -1, 0, 1, 0, 'Info', 'users/admin.php', ''),
(11, 'admin', -1, 1, 1, 1, 'Settings', '', ''),
(12, 'admin', 11, 0, 1, 2, 'Security', 'users/admin_security.php', ''),
(13, 'admin', 11, 0, 1, 3, 'CSS', 'users/admin_css.php', ''),
(14, 'admin', -1, 0, 1, 4, 'Users', 'users/admin_users.php', ''),
(15, 'admin', -1, 0, 1, 5, 'Groups', 'users/admin_groups.php', ''),
(16, 'admin', -1, 0, 1, 6, 'Pages', 'users/admin_pages.php', ''),
(17, 'admin', 20, 0, 1, 7, 'Settings', 'users/admin_email.php', ''),
(18, 'admin', -1, 0, 1, 8, 'Menus', 'users/admin_menus.php', ''),
(20, 'admin', -1, 1, 1, 7, 'Email', '', ''),
(21, 'admin', 20, 0, 1, 99999, 'Email Verify Template', 'users/admin_email_template.php?type=verify', ''),
(22, 'admin', 20, 0, 1, 99999, 'Forgot Password Template', 'users/admin_email_template.php?type=forgot', ''),
(23, 'main', 6, 0, 0, 99999, 'Verify Resend', 'users/verify_resend.php', ''),
(24, 'admin', 11, 0, 1, 0, 'General', 'users/admin_general.php', ''),
(25, 'admin', 11, 0, 1, 1, 'Redirects', 'users/admin_redirects.php', ''),
(26, 'admin', -1, 0, 1, 99999, 'Add User(s)', 'users/admin_users_add.php', ''),
(27, 'admin', 20, 0, 1, 99999, 'Test', 'users/admin_email_test.php', ''),
(28, 'admin', -1, 1, 1, 99999, 'System', '', ''),
(29, 'admin', 28, 0, 1, 99999, 'Updates', 'users/admin_updates.php', ''),
(30, 'admin', 28, 0, 1, 99999, 'Backup', 'users/admin_backup.php', ''),
(31, 'admin', 28, 0, 1, 99999, 'Restore', 'users/admin_restore.php', ''),
(32, 'admin', 28, 0, 1, 99999, 'Status', 'users/admin_status.php', ''),
(33, 'admin', 28, 0, 1, 99999, 'PHP Info', 'users/admin_phpinfo.php', ''),
(34, 'admin', 11, 0, 1, 99999, 'Registration', 'users/admin_registration.php', ''),
(35, 'admin', 11, 0, 1, 99999, 'Google Login', 'users/admin_googlelogin.php', ''),
(36, 'admin', 11, 0, 1, 99999, 'Facebook Login', 'users/admin_facebooklogin.php', '');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `page` varchar(100) NOT NULL,
  `private` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `page`, `private`) VALUES
(1, 'index.php', 0),
(2, 'z_us_root.php', 0),
(4, 'users/admin.php', 1),
(6, 'users/admin_pages.php', 1),
(14, 'users/forgot_password.php', 0),
(15, 'users/password_reset.php', 0),
(17, 'users/init.php', 0),
(18, 'users/join.php', 0),
(20, 'users/login.php', 0),
(21, 'users/logout.php', 0),
(22, 'users/profile.php', 1),
(25, 'users/verify.php', 0),
(26, 'users/verify_resend.php', 0),
(28, 'usersc/empty.php', 0),
(29, 'users/admin_css.php', 0),
(30, 'users/admin_email.php', 0),
(31, 'users/admin_general.php', 0),
(32, 'users/admin_security.php', 0),
(33, 'users/admin_email_test.php', 0),
(36, 'users/admin_page.php', 0),
(37, 'users/admin_group.php', 0),
(38, 'users/admin_groups.php', 0),
(39, 'users/admin_user.php', 0),
(40, 'users/admin_users.php', 0),
(42, 'users/db_cred.php', 0),
(43, 'users/admin_menus.php', 0),
(44, 'users/admin_menu.php', 0),
(45, 'users/admin_menu_item.php', 0),
(46, 'users/admin_email_template.php', 0),
(48, 'users/index.php', 0),
(49, 'contact.php', 0),
(50, 'gallery.php', 1),
(51, 'join.php', 0),
(52, 'login.php', 0),
(55, 'profile.php', 0),
(57, 'users/admin_redirects.php', 0),
(59, 'users/admin_users_add.php', 0),
(60, 'blocked.php', 0),
(61, 'forgot_password.php', 0),
(62, 'users/blocked.php', 0),
(63, 'password_reset.php', 0),
(64, 'verify.php', 0),
(65, 'verify_resend.php', 0),
(66, 'users/admin_updates.php', 0),
(68, 'users/admin_backup.php', 0),
(69, 'users/admin_restore.php', 0),
(70, 'users/admin_status.php', 0),
(71, 'users/admin_phpinfo.php', 0),
(73, 'users/admin_registration.php', 0),
(74, 'users/admin_groups.php', 0),
(75, 'users/admin_googlelogin.php', 0),
(76, 'users/fb-callback.php', 0),
(77, 'users/oauth_success.php', 0),
(78, 'users/admin_facebooklogin.php', 0);

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
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`);

--
-- Inserting data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `bio`) VALUES
(1, 1, '<h1>This is the Admin\'s bio.</h1>'),
(2, 2, '<h1>This is the User\'s bio.</h1>');

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`) VALUES
(1, 'User'),
(2, 'Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `groups_pages`
--

CREATE TABLE `groups_pages` (
  `id` int(11) NOT NULL,
  `group_id` int(15) NOT NULL,
  `page_id` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups_pages`
--

INSERT INTO `groups_pages` (`id`, `group_id`, `page_id`) VALUES
(2, 2, 27),
(3, 1, 24),
(4, 1, 22),
(5, 2, 13),
(6, 2, 12),
(7, 1, 11),
(8, 2, 10),
(9, 2, 9),
(10, 2, 8),
(11, 2, 7),
(12, 2, 6),
(13, 2, 5),
(14, 2, 4),
(15, 1, 3),
(21, 2, 36),
(22, 1, 50),
(23, 1, 56);

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
  `fbcallback` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `site_name`, `site_url`, `install_location`, `copyright_message`, `version`, `language`, `site_offline`, `debug_mode`, `query_count`, `track_guest`, `recaptcha`, `force_ssl`, `css_sample`, `css1`, `css2`, `css3`, `mail_method`, `smtp_server`, `smtp_port`, `smtp_transport`, `email_login`, `email_pass`, `from_name`, `from_email`, `email_act`, `recaptcha_private`, `recaptcha_public`, `email_verify_template`, `forgot_password_template`, `redirect_login`, `redirect_logout`, `redirect_deny_nologin`, `redirect_deny_noperm`, `redirect_referrer_login`, `session_timeout`, `allow_remember_me`, `backup_dest`, `agreement`, `glogin`, `fblogin`, `gid`, `gsecret`, `fbid`, `fbsecret`, `gcallback`, `fbcallback`) VALUES
(1, '', '', '', 'US', '5.0.0a', 'en', 0, 1, 1, 1, 0, 0, 1, 'users/css/color_schemes/standard.css', 'users/css/blank.css', 'users/css/blank.css', 'smtp', '', 25, 'TLS', '', '', 'UserSpice Admin', '', 0, '', '', '&lt;p&gt;Congratulations {{fname}},&lt;/p&gt;\n&lt;p&gt;Thanks for signing up Please click the link below to verify your email address.&lt;/p&gt;\n&lt;p&gt;{{url}}&lt;/p&gt;\n&lt;p&gt;Once you verify your email address you will be ready to login!&lt;/p&gt;\n&lt;p&gt;Sincerely,&lt;/p&gt;\n&lt;p&gt;-The {{sitename}} Team-&lt;/p&gt;', '&lt;p&gt;Hello {{fname}},&lt;/p&gt;\n&lt;p&gt;You are receiving this email because a request was made to reset your password. If this was not you, you may disgard this email.&lt;/p&gt;\n&lt;p&gt;If this was you, click the link below to continue with the password reset process.&lt;/p&gt;\n&lt;p&gt;{{url}}&lt;/p&gt;\n&lt;p&gt;Sincerely,&lt;/p&gt;\n&lt;p&gt;-The {{sitename}} Team-&lt;/p&gt;', 'users/profile.php', 'index.php', 'users/login.php', 'index.php', 1, 3600, 1, 'backup_userspice/', 'Welcome to our website. If you continue to browse and use this website, you are agreeing to comply with and be bound by the following terms and conditions of use, which together with our privacy policy govern our relationship with you in relation to this website. If you disagree with any part of these terms and conditions, please do not use our website.\r\n\r\nThe use of this website is subject to the following terms of use:\r\n\r\nThe content of the pages of this website is for your general information and use only. It is subject to change without notice.\r\n\r\nThis website uses cookies to monitor browsing preferences. If you do allow cookies to be used, the following personal information may be stored by us for use by third parties.\r\n\r\nNeither we nor any third parties provide any warranty or guarantee as to the accuracy, timeliness, performance, completeness or suitability of the information and materials found or offered on this website for any particular purpose.\r\n\r\nYou acknowledge that such information and materials may contain inaccuracies or errors and we expressly exclude liability for any such inaccuracies or errors to the fullest extent permitted by law.\r\n\r\nYour use of any information or materials on this website is entirely at your own risk, for which we shall not be liable. It shall be your own responsibility to ensure that any products, services or information available through this website meet your specific requirements.\r\n\r\nThis website contains material which is owned by or licensed to us. This material includes, but is not limited to, the design, layout, look, appearance and graphics. Reproduction is prohibited other than in accordance with the copyright notice, which forms part of these terms and conditions.\r\nAll trade marks reproduced in this website which are not the property of, or licensed to, the operator are acknowledged on the website.\r\n\r\nUnauthorised use of this website may give rise to a claim for damages and/or be a criminal offence.\r\n\r\nFrom time to time this website may also include links to other websites. These links are provided for your convenience to provide further information. They do not signify that we endorse the website(s). We have no responsibility for the content of the linked website(s).', 0, 0, '', '', '', '', 'https://us.raysee.net/users/helpers/gcallback.php', 'https://us.raysee.net/users/helpers/fbcallback.php');

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
(1, 'userspicephp@gmail.com', 'admin', '$2y$12$iE87plmPoyV1rjoZPZENLOi55frC3HrQAz70VI/ud.mzbco2wz/1S', 'Admin', 'User', 1, 263, 1, 0, 'UserSpice', '', '', '', '', '', '', '', 'America/Toronto', '2016-01-01 00:00:00', '2016-10-04 07:25:29', 1, '322418', '', 0, '&lt;p&gt;This is the admin user default bio&lt;/p&gt;', '', ''),
(2, 'noreply@userspice.com', 'user', '$2y$12$HZa0/d7evKvuHO8I3U8Ff.pOjJqsGTZqlX8qURratzP./EvWetbkK', 'user', 'user', 1, 10, 1, 0, 'none', '', '', '', '', '', '', '', '', '2016-01-02 00:00:00', '2016-09-10 17:16:31', 1, '970748', '', 1, 'This is the user user bio', '', '');

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

-- --------------------------------------------------------

--
-- Table structure for table `groups_users_raw`
--

CREATE TABLE `groups_users_raw` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_is_group` BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups_users_raw`
--

INSERT INTO `groups_users_raw` (`id`, `user_id`, `group_id`) VALUES
(100, 1, 1),
(101, 1, 2),
(102, 2, 1);

--
-- Indexes for dumped tables
--

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
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groups_pages`
--
ALTER TABLE `groups_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);
--  ADD KEY `menu_id` (`menu_id`); -- no column in `groups_pages`

--
-- Indexes for table `groups_menus`
--
ALTER TABLE `groups_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `menu_id` (`menu_id`);

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
-- Indexes for table `groups_users_raw`
--
ALTER TABLE `groups_users_raw`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_is_group` (`user_is_group`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `groups_pages`
--
ALTER TABLE `groups_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `users_online`
--
ALTER TABLE `users_online`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `users_session`
--
ALTER TABLE `users_session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
--
-- AUTO_INCREMENT for table `groups_users_raw`
--
ALTER TABLE `groups_users_raw`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

--
-- CREATE VIEW for view `groups_users`
--
CREATE OR REPLACE VIEW groups_users AS
    SELECT id, user_id, group_id
    FROM groups_users_raw
    WHERE user_is_group = 0
    UNION
    SELECT ug1.id+ug2.id*1000 AS id, ug1.user_id, ug2.group_id
    FROM groups_users_raw ug1
    JOIN groups_users_raw ug2 ON (ug1.group_id = ug2.user_id)
    WHERE ug2.user_is_group = 1
    AND ug1.user_is_group = 0
    UNION
    SELECT ug1.id+ug2.id*1000+ug3.id*1000000 AS id, ug1.user_id, ug3.group_id
    FROM groups_users_raw ug1
    JOIN groups_users_raw ug2 ON (ug1.group_id = ug2.user_id)
    JOIN groups_users_raw ug3 ON (ug2.group_id = ug3.user_id)
    WHERE ug3.user_is_group = 1
    AND ug2.user_is_group = 1
    AND ug1.user_is_group = 0
    UNION
    SELECT ug1.id+ug2.id*1000+ug3.id*1000000+ug4.id*1000000000 AS id, ug1.user_id, ug4.group_id
    FROM groups_users_raw ug1
    JOIN groups_users_raw ug2 ON (ug1.group_id = ug2.user_id)
    JOIN groups_users_raw ug3 ON (ug2.group_id = ug3.user_id)
    JOIN groups_users_raw ug4 ON (ug3.group_id = ug4.user_id)
    WHERE ug4.user_is_group = 1
    AND ug3.user_is_group = 1
    AND ug2.user_is_group = 1
    AND ug1.user_is_group = 0
