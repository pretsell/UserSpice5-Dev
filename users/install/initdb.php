<?php
/*
 * initdb.PHP
 * Create the tables/indices for the database and fill them with data appropriately
 * This needs to be done in PHP rather than in mysql because of the table prefixes
 */

require_once('init.php');
$prefix = configGet('mysql/prefix');
$engine = "MyISAM";
$charset = "utf8";
$collate = "utf8_bin";
echo "At some point we need to make sure this is the same US_PAGE_PATH we used in mkpages.php<br />\n";
$US_PAGE_PATH = getPagePath();
$US_PAGE_PATH = array_pop($US_PAGE_PATH);
if (substr($US_PAGE_PATH, -1, 1) == '/') {
    $US_PAGE_PATH = substr($US_PAGE_PATH, 0, -1); // strip trailing slash
}
$init_commands = [
    "CREATE TABLE `{$prefix}audit` (
          `id` int(11) NOT NULL,
          `user_id` int(11) DEFAULT NULL,
          `page` varchar(250) COLLATE $collate NOT NULL,
          `state` varchar(25) COLLATE $collate NOT NULL,
          `ip` varchar(80) COLLATE $collate NOT NULL,
          `viewed` tinyint(1) NOT NULL,
          `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=$engine DEFAULT CHARSET=$charset COLLATE=$collate",
    "CREATE TABLE `{$prefix}field_defs` (
          `id` int(11) NOT NULL,
          `name` varchar(50) COLLATE $collate NOT NULL,
          `alias` varchar(50) COLLATE $collate DEFAULT NULL,
          `display_lang` varchar(50) COLLATE $collate NOT NULL,
          `min` int(11) DEFAULT NULL,
          `max` int(11) DEFAULT NULL,
          `min_val` varchar(50) COLLATE $collate DEFAULT NULL,
          `max_val` varchar(50) COLLATE $collate DEFAULT NULL,
          `required` tinyint(1) DEFAULT NULL,
          `unique_in_table` varchar(50) COLLATE $collate DEFAULT NULL,
          `match_field` varchar(50) COLLATE $collate DEFAULT NULL,
          `is_numeric` tinyint(1) DEFAULT NULL,
          `valid_email` tinyint(1) DEFAULT NULL,
          `regex` varchar(500) COLLATE $collate DEFAULT NULL,
          `regex_display` varchar(500) COLLATE $collate DEFAULT NULL
      ) ENGINE=$engine DEFAULT CHARSET=$charset COLLATE=$collate",
    "INSERT INTO `{$prefix}field_defs` (`id`, `name`, `alias`, `display_lang`, `min`, `max`, `required`, `unique_in_table`, `match_field`, `is_numeric`, `valid_email`, `regex`, `regex_display`) VALUES
        (1, 'users.username', 'username', 'USERNAME', 1, 150, 1, 'users', NULL, NULL, NULL, '/^[^\\\\t !@#$%^&*(){}\\\\[\\\\]`~\\\\|]*$/', 'No spaces or special characters'),
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
        (13, 'groups.grouptype_id', 'grouptype_id', 'GROUPTYPE_LABEL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)",
    "CREATE TABLE `{$prefix}groups` (
          `id` int(11) NOT NULL,
          `grouptype_id` int(11) DEFAULT NULL,
          `name` varchar(150) NOT NULL,
          `short_name` varchar(25) DEFAULT NULL,
          `admin` tinyint(1) NOT NULL,
          `is_role` tinyint(1) NOT NULL DEFAULT '0'
      ) ENGINE=$engine DEFAULT CHARSET=$charset",
    "INSERT INTO `{$prefix}groups` (`id`, `grouptype_id`, `name`, `short_name`, `admin`, `is_role`) VALUES
        (1, 0, 'Users', '', 0, 0),
        (2, 0, 'Administrators', '', 1, 0),
        (54, 8, 'President', 'Pres', 1, 1),
        (55, 8, 'Vice President', 'VP', 0, 1),
        (56, 8, 'Acme Multi-National Corp International Division', 'Acme', 0, 0),
        (57, 11, 'Zoological Department', 'ZD', 0, 0),
        (58, 11, 'Department Head', 'DH', 0, 1)",
    "CREATE TABLE `{$prefix}groups_menus` (
          `id` int(11) NOT NULL,
          `group_id` int(11) NOT NULL,
          `menu_id` int(11) NOT NULL
      ) ENGINE=$engine DEFAULT CHARSET=$charset COLLATE=$collate",
    "INSERT INTO `{$prefix}groups_menus` (`id`, `group_id`, `menu_id`) VALUES
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
        (43, 2, 33)",
    "CREATE TABLE `{$prefix}groups_pages` (
          `id` int(11) NOT NULL,
          `allow_deny` char(1) NOT NULL DEFAULT 'A',
          `group_id` int(15) DEFAULT NULL,
          `grouprole_id` int(11) DEFAULT NULL,
          `page_id` int(15) NOT NULL,
          `auth` varchar(50) NOT NULL
      ) ENGINE=$engine DEFAULT CHARSET=$charset",
    "INSERT INTO `{$prefix}groups_pages` (`id`, `allow_deny`, `group_id`, `grouprole_id`, `page_id`, `auth`) VALUES
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
        (60, 'A', 56, NULL, 93, '')",
    "CREATE TABLE `{$prefix}groups_roles_users` (
          `id` int(11) NOT NULL,
          `group_id` int(11) DEFAULT NULL,
          `role_group_id` int(11) DEFAULT NULL,
          `user_id` int(11) NOT NULL
      ) ENGINE=$engine DEFAULT CHARSET=$charset COLLATE=$collate",
    "INSERT INTO `{$prefix}groups_roles_users` (`id`, `group_id`, `role_group_id`, `user_id`) VALUES
        (6, 41, 8, 4),
        (7, 43, 14, 1),
        (8, 43, 14, 2),
        (9, 43, 23, 5),
        (10, 39, 8, 2),
        (11, 52, 10, 3),
        (12, 56, 54, 2),
        (13, 57, 58, 2)",
    "CREATE TABLE `{$prefix}groups_users_raw` (
          `id` int(11) NOT NULL,
          `group_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `user_is_group` tinyint(1) NOT NULL DEFAULT '0'
      ) ENGINE=$engine DEFAULT CHARSET=$charset",
    "INSERT INTO `{$prefix}groups_users_raw` (`id`, `group_id`, `user_id`, `user_is_group`) VALUES
        (100, 1, 1, 0),
        (102, 1, 2, 0),
        (163, 1, 3, 0),
        (164, 1, 4, 0),
        (165, 1, 5, 0),
        (166, 1, 6, 0),
        (101, 2, 1, 0),
        (160, 54, 2, 0),
        (159, 56, 2, 0),
        (161, 57, 2, 0),
        (162, 58, 2, 0)",
    "CREATE TABLE `{$prefix}grouptypes` (
          `id` int(11) NOT NULL,
          `name` varchar(150) NOT NULL,
          `short_name` varchar(15) NOT NULL
      ) ENGINE=$engine DEFAULT CHARSET=$charset COLLATE=$collate",
    "INSERT INTO `{$prefix}grouptypes` (`id`, `name`, `short_name`) VALUES
        (8, 'International Division', 'ID'),
        (9, 'Region', 'Region'),
        (10, 'Team', 'Team'),
        (11, 'Department', 'Dept')",
    "CREATE TABLE `{$prefix}menus` (
          `id` int(10) NOT NULL,
          `menu_title` varchar(255) NOT NULL,
          `parent` int(10) NOT NULL,
          `dropdown` tinyint(1) NOT NULL,
          `logged_in` tinyint(1) NOT NULL,
          `display_order` int(10) NOT NULL,
          `label` varchar(255) NOT NULL,
          `link` varchar(255) NOT NULL,
          `link_args` varchar(500) NOT NULL DEFAULT '',
          `page_id` int(11) DEFAULT NULL,
          `icon_class` varchar(255) NOT NULL
      ) ENGINE=$engine DEFAULT CHARSET=$charset",
    "INSERT INTO `{$prefix}menus` (`id`, `menu_title`, `parent`, `dropdown`, `logged_in`, `display_order`, `label`, `link`, `link_args`, `page_id`, `icon_class`) VALUES
        (1,  'main',  -1, 0, 1, 0,     'Home',                     '', '',             NULL, 'fa fa-fw fa-home'),
        (2,  'main',  -1, 0, 1, 2,     'Dashboard',                '', '',             4,    'fa fa-fw fa-cogs'),
        (3,  'main',  -1, 1, 1, 1,     '{{username}}',             '', '',             NULL, 'fa fa-fw fa-user'),
        (4,  'main',   3, 0, 1, 1,     'Profile',                  '', '',             22,   'fa fa-fw fa-home'),
        (5,  'main',   3, 0, 1, 1,     'Logout',                   '', '',             21,   'fa fa-fw fa-home'),
        (6,  'main',  -1, 1, 1, 3,     'Help',                     '', '',             NULL, 'fa fa-fw fa-life-ring'),
        (8,  'main',  -1, 0, 0, 1,     'Register',                 '', '',             18,   'fa fa-fw fa-plus-square'),
        (9,  'main',  -1, 0, 0, 2,     'Log In',                   '', '',             20,   'fa fa-fw fa-sign-in'),
        (10, 'admin', -1, 0, 1, 10,    'Info',                     '', '',             4,    ''),
        (11, 'admin', -1, 0, 1, 20,    'Settings',                 '', '',             32,   ''),
        (14, 'admin', -1, 0, 1, 30,    'Users',                    '', '',             40,   ''),
        (16, 'admin', -1, 0, 1, 50,    'Pages',                    '', '',             6,    ''),
        (17, 'admin', 20, 0, 1, 10,    'Settings',                 '', '',             30,   ''),
        (18, 'admin', -1, 0, 1, 60,    'Menus',                    '', '',             43,   ''),
        (20, 'admin', -1, 1, 1, 70,    'Email',                    '', '',             NULL, ''),
        (21, 'admin', 20, 0, 1, 20,    'Email Verify Template',    '', '?type=verify', 46,   ''),
        (22, 'admin', 20, 0, 1, 30,    'Forgot Password Template', '', '?type=forgot', 46,   ''),
        (23, 'main',   6, 0, 0, 99999, 'Verify Resend',            '', '',             26,   ''),
        (26, 'admin', -1, 0, 1, 80,    'Add User(s)',              '', '',             59,   ''),
        (27, 'admin', 20, 0, 1, 40,    'Test',                     '', '',             33,   ''),
        (28, 'admin', -1, 1, 1, 90,    'System',                   '', '',             NULL, ''),
        (29, 'admin', 28, 0, 1, 10,    'Updates',                  '', '',             66,   ''),
        (30, 'admin', 28, 0, 1, 20,    'Backup',                   '', '',             68,   ''),
        (31, 'admin', 28, 0, 1, 30,    'Restore',                  '', '',             69,   ''),
        (32, 'admin', 28, 0, 1, 40,    'Status',                   '', '',             70,   ''),
        (33, 'admin', 28, 0, 1, 50,    'PHP Info',                 '', '',             71,   ''),
        (38, 'admin', -1, 1, 1, 40,    'Groups',                   '', '',             NULL, ''),
        (39, 'admin', 38, 0, 1, 10,    'Groups',                   '', '',             74,   ''),
        (40, 'admin', 38, 0, 1, 20,    'Group Roles',              '', '',             84,   ''),
        (41, 'admin', 38, 0, 1, 30,    'Group Types',              '', '',             85,   '')",
    "CREATE TABLE `{$prefix}pages` (
          `id` int(11) NOT NULL,
          `page` varchar(100) NOT NULL,
          `private` tinyint(11) NOT NULL DEFAULT '0',
          `title_token` varchar(100) DEFAULT NULL,
          `breadcrumb_parent_page_id` int(11) DEFAULT NULL,
          `after_create` tinyint(1) NOT NULL DEFAULT '0',
          `after_edit` tinyint(1) NOT NULL DEFAULT '0',
          `after_delete` tinyint(1) NOT NULL DEFAULT '0',
          `after_create_redirect` varchar(255) DEFAULT NULL,
          `after_edit_redirect` varchar(255) DEFAULT NULL,
          `after_delete_redirect` varchar(255) DEFAULT NULL,
          `site_offline_access` tinyint(1) NOT NULL DEFAULT '0',
      ) ENGINE=$engine DEFAULT CHARSET=$charset",
    "INSERT INTO `{$prefix}pages` (`id`, `page`, `private`, `title_token`, `breadcrumb_parent_page_id`, `site_offline_access`) VALUES
        (1, '{$US_PAGE_PATH}/index.php',                 0, 'INDEX_TITLE',                NULL, 0),
        (6, '{$US_PAGE_PATH}/admin_pages.php',           1, 'ADMIN_PAGES_TITLE',          14,   0),
        (14, '{$US_PAGE_PATH}/forgot_password.php',      0, 'FORGOT_PASSWORD_TITLE',      15,   0),
        (15, '{$US_PAGE_PATH}/password_reset.php',       0, 'PASSWORD_RESET_TITLE',       NULL, 0),
        (18, '{$US_PAGE_PATH}/join.php',                 0, 'JOIN_TITLE',                 NULL, 0),
        (20, '{$US_PAGE_PATH}/login.php',                0, 'LOGIN_TITLE',                NULL, 1),
        (21, '{$US_PAGE_PATH}/logout.php',               0, 'LOGOUT_TITLE',               NULL, 0),
        (22, '{$US_PAGE_PATH}/profile.php',              1, 'PROFILE_TITLE',              NULL, 0),
        (26, '{$US_PAGE_PATH}/verify_resend.php',        0, 'VERIFY_RESEND_TITLE',        NULL, 0),
        (30, '{$US_PAGE_PATH}/admin_email.php',          1, 'ADMIN_EMAIL_TITLE',          93,   0),
        (32, '{$US_PAGE_PATH}/admin_settings.php',       1, 'ADMIN_SETTINGS_TITLE',       93,   0),
        (33, '{$US_PAGE_PATH}/admin_email_test.php',     1, 'ADMIN_EMAIL_TEST_TITLE',     30,   0),
        (36, '{$US_PAGE_PATH}/admin_page.php',           1, 'ADMIN_PAGE_TITLE',           93,   0),
        (39, '{$US_PAGE_PATH}/admin_user.php',           1, 'ADMIN_USER_TITLE',           40,   0),
        (40, '{$US_PAGE_PATH}/admin_users.php',          1, 'ADMIN_USERS_TITLE',          93,   0),
        (43, '{$US_PAGE_PATH}/admin_menus.php',          0, 'ADMIN_MENUS_TITLE',          93,   0),
        (44, '{$US_PAGE_PATH}/admin_menu.php',           0, 'ADMIN_MENU_TITLE',           43,   0),
        (45, '{$US_PAGE_PATH}/admin_menu_item.php',      0, 'ADMIN_MENU_ITEM_TITLE',      44,   0),
        (46, '{$US_PAGE_PATH}/admin_email_template.php', 1, 'ADMIN_EMAIL_TEMPLATE_TITLE', 93,   0),
        (49, '{$US_PAGE_PATH}/contact.php',              0, 'CONTACT_TITLE',              NULL, 0),
        (50, '{$US_PAGE_PATH}/gallery.php',              1, 'GALLERY_TITLE',              NULL, 0),
        (59, '{$US_PAGE_PATH}/admin_users_add.php',      1, 'ADMIN_USERS_ADD_TITLE',      40,   0),
        (62, '{$US_PAGE_PATH}/blocked.php',              0, 'BLOCKED_TITLE',              NULL, 0),
        (66, '{$US_PAGE_PATH}/admin_updates.php',        1, 'ADMIN_UPDATES_TITLE',        93,   0),
        (68, '{$US_PAGE_PATH}/admin_backup.php',         1, 'ADMIN_BACKUP_TITLE',         93,   0),
        (69, '{$US_PAGE_PATH}/admin_restore.php',        1, 'ADMIN_RESTORE_TITLE',        93,   0),
        (70, '{$US_PAGE_PATH}/admin_status.php',         1, 'ADMIN_STATUS_TITLE',         93,   0),
        (71, '{$US_PAGE_PATH}/admin_phpinfo.php',        1, 'ADMIN_PHPINFO_TITLE',        93,   0),
        (74, '{$US_PAGE_PATH}/admin_groups.php',         1, 'ADMIN_GROUPS_TITLE',         93,   0),
        (80, '{$US_PAGE_PATH}/admin_group.php',          1, 'ADMIN_GROUP_TITLE',          74,   0),
        (82, '{$US_PAGE_PATH}/oauth_denied.php',         0, 'OAUTH_DENIED_TITLE',         NULL, 0),
        (83, '{$US_PAGE_PATH}/admin_role.php',           1, 'ADMIN_ROLE_TITLE',           84,   0),
        (84, '{$US_PAGE_PATH}/admin_roles.php',          1, 'ADMIN_ROLES_TITLE',          93,   0),
        (85, '{$US_PAGE_PATH}/admin_grouptypes.php',     1, 'ADMIN_GROUPTYPES_TITLE',     93,   0),
        (86, '{$US_PAGE_PATH}/admin_grouptype.php',      1, 'ADMIN_GROUPTYPE_TITLE',      85,   0),
        (87, '{$US_PAGE_PATH}/nologin.php',              0, 'NOLOGIN_TITLE',              NULL, 0),
        (93, '{$US_PAGE_PATH}/admin.php',                1, 'ADMIN_TITLE',                NULL, 0),
        (94, '{$US_PAGE_PATH}/admin_general.php',        0, 'ADMIN_GENERAL_TITLE',        93,   0),
        (95, '{$US_PAGE_PATH}/verify.php',               0, 'VERIFY_TITLE',               NULL, 0),
        (96, '{$US_PAGE_PATH}/admin_pages_old.php',      0, 'ADMIN_PAGES_OLD_TITLE',      93,   0),
        (97, '{$US_PAGE_PATH}/admin_page_old.php',       0, 'ADMIN_PAGE_OLD_TITLE',       96,   0),
        (98, '{$US_PAGE_PATH}/offline.php',              0, 'OFFLINE_TITLE',              NULL, 0),",
    "CREATE TABLE `{$prefix}profiles` (
          `id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `bio` text NOT NULL
      ) ENGINE=$engine DEFAULT CHARSET=$charset",
    "INSERT INTO `{$prefix}profiles` (`id`, `user_id`, `bio`) VALUES
        (1, 3, 'This is your bio'),
        (2, 4, 'This is your bio'),
        (3, 5, 'This is your bio'),
        (4, 6, 'This is your bio')",
    "CREATE TABLE `{$prefix}settings` (
          `id` int(50) NOT NULL,
          `site_name` varchar(100) NOT NULL,
          `site_url` varchar(255) NOT NULL,
          `install_location` varchar(255) NOT NULL,
          `copyright_message` varchar(255) NOT NULL,
          `version` varchar(255) NOT NULL,
          `site_language` varchar(255) NOT NULL,
          `site_offline` tinyint(1) NOT NULL,
          `debug_mode` tinyint(1) NOT NULL,
          `query_count` tinyint(1) NOT NULL,
          `track_guest` tinyint(1) NOT NULL,
          `recaptcha` tinyint(1) NOT NULL DEFAULT '0',
          `force_ssl` tinyint(1) NOT NULL,
          `css_sample` tinyint(1) NOT NULL,
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
          `email_act` tinyint(1) NOT NULL,
          `recaptcha_private` varchar(255) NOT NULL,
          `recaptcha_public` varchar(255) NOT NULL,
          `email_verify_template` longtext NOT NULL,
          `forgot_password_template` longtext NOT NULL,
          `redirect_login` varchar(255) NOT NULL,
          `redirect_logout` varchar(255) NOT NULL,
          `redirect_deny_nologin` varchar(255) NOT NULL,
          `redirect_deny_noperm` varchar(255) NOT NULL,
          `redirect_site_offline` varchar(255) NOT NULL,
          `multi_row_after_create` tinyint(1) NOT NULL,
          `multi_row_after_edit` tinyint(1) NOT NULL,
          `multi_row_after_delete` tinyint(1) NOT NULL,
          `single_row_after_create` tinyint(1) NOT NULL,
          `single_row_after_edit` tinyint(1) NOT NULL,
          `single_row_after_delete` tinyint(1) NOT NULL,
          `redirect_referrer_login` tinyint(1) NOT NULL,
          `session_timeout` int(10) NOT NULL,
          `allow_remember_me` tinyint(1) NOT NULL,
          `backup_dest` varchar(255) NOT NULL,
          `agreement` longtext NOT NULL,
          `glogin` tinyint(1) NOT NULL,
          `fblogin` tinyint(1) NOT NULL,
          `gid` varchar(255) NOT NULL,
          `gsecret` varchar(255) NOT NULL,
          `fbid` varchar(255) NOT NULL,
          `fbsecret` varchar(255) NOT NULL,
          `gcallback` varchar(255) NOT NULL,
          `fbcallback` varchar(255) NOT NULL,
          `allow_username_change` tinyint(1) NOT NULL DEFAULT '1'
      ) ENGINE=$engine DEFAULT CHARSET=$charset",
    "INSERT INTO `{$prefix}settings` (`id`, `site_name`, `site_url`,
            `install_location`, `copyright_message`, `version`, `site_language`,
            `site_offline`, `debug_mode`, `query_count`, `track_guest`,
            `recaptcha`, `force_ssl`, `css_sample`, `css1`, `css2`, `css3`,
            `mail_method`, `smtp_server`, `smtp_port`, `smtp_transport`,
            `email_login`, `email_pass`, `from_name`, `from_email`, `email_act`,
            `recaptcha_private`, `recaptcha_public`, `email_verify_template`,
            `forgot_password_template`,
            `redirect_login`, `redirect_logout`, `redirect_deny_nologin`,
            `redirect_deny_noperm`, `redirect_site_offline`,
            `multi_row_after_create`, `multi_row_after_edit`,
            `multi_row_after_delete`, `single_row_after_create`,
            `single_row_after_edit`, `single_row_after_delete`,
            `redirect_referrer_login`, `session_timeout`, `allow_remember_me`,
            `backup_dest`, `agreement`, `glogin`, `fblogin`, `gid`, `gsecret`,
            `fbid`, `fbsecret`, `gcallback`, `fbcallback`, `allow_username_change`) VALUES
        (1, 'UserSpice5', 'http://localhost/UserSpice5-Dev/',
            '', 'US', '5.0.0a', 'english.php',
            0, 1, 1, 1,
            0, 0, 1, 'core/css/color_schemes/standard.css', 'core/css/blank.css', 'core/css/blank.css',
            'smtp', '', 25, 'TLS',
            '', '', 'UserSpice Admin', '', 0,
            '', '', '&lt;p&gt;Congratulations {{fname}},&lt;/p&gt;\n&lt;p&gt;Thanks for signing up Please click the link below to verify your email address.&lt;/p&gt;\n&lt;p&gt;{{url}}&lt;/p&gt;\n&lt;p&gt;Once you verify your email address you will be ready to login!&lt;/p&gt;\n&lt;p&gt;Sincerely,&lt;/p&gt;\n&lt;p&gt;-The {{sitename}} Team-&lt;/p&gt;',
            '&lt;p&gt;Hello {{fname}},&lt;/p&gt;\n&lt;p&gt;You are receiving this email because a request was made to reset your password. If this was not you, you may disgard this email.&lt;/p&gt;\n&lt;p&gt;If this was you, click the link below to continue with the password reset process.&lt;/p&gt;\n&lt;p&gt;{{url}}&lt;/p&gt;\n&lt;p&gt;Sincerely,&lt;/p&gt;\n&lt;p&gt;-The {{sitename}} Team-&lt;/p&gt;',
            'profile.php', 'index.php', 'login.php',
            'index.php', 'offline.php',
            1, 1, 1,
            2, 2, 2,
            1, 86400, 1,
            'backup_userspice/', 'Welcome to our website. If you continue to browse and use this website, you are agreeing to comply with and be bound by the following terms and conditions of use, which together with our privacy policy govern our relationship with you in relation to this website. If you disagree with any part of these terms and conditions, please do not use our website.\r\n\r\nThe use of this website is subject to the following terms of use:\r\n\r\nThe content of the pages of this website is for your general information and use only. It is subject to change without notice.\r\n\r\nThis website uses cookies to monitor browsing preferences. If you do allow cookies to be used, the following personal information may be stored by us for use by third parties.\r\n\r\nNeither we nor any third parties provide any warranty or guarantee as to the accuracy, timeliness, performance, completeness or suitability of the information and materials found or offered on this website for any particular purpose.\r\n\r\nYou acknowledge that such information and materials may contain inaccuracies or errors and we expressly exclude liability for any such inaccuracies or errors to the fullest extent permitted by law.\r\n\r\nYour use of any information or materials on this website is entirely at your own risk, for which we shall not be liable. It shall be your own responsibility to ensure that any products, services or information available through this website meet your specific requirements.\r\n\r\nThis website contains material which is owned by or licensed to us. This material includes, but is not limited to, the design, layout, look, appearance and graphics. Reproduction is prohibited other than in accordance with the copyright notice, which forms part of these terms and conditions.\r\nAll trade marks reproduced in this website which are not the property of, or licensed to, the operator are acknowledged on the website.\r\n\r\nUnauthorised use of this website may give rise to a claim for damages and/or be a criminal offence.\r\n\r\nFrom time to time this website may also include links to other websites. These links are provided for your convenience to provide further information. They do not signify that we endorse the website(s). We have no responsibility for the content of the linked website(s).', 0, 0, '', '',
            '', '', 'https://us.raysee.net/users/helpers/gcallback.php', 'https://us.raysee.net/users/helpers/fbcallback.php', 1)",
    "CREATE TABLE `{$prefix}users` (
          `id` int(11) NOT NULL,
          `email` varchar(155) NOT NULL,
          `username` varchar(255) NOT NULL,
          `password` varchar(255) NOT NULL,
          `fname` varchar(255) NOT NULL,
          `lname` varchar(255) NOT NULL,
          `admin` tinyint(1) NOT NULL DEFAULT '0',
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
          `active` tinyint(1) NOT NULL,
          `bio` longtext NOT NULL,
          `google_uid` varchar(255) NOT NULL,
          `facebook_uid` varchar(255) NOT NULL
      ) ENGINE=$engine DEFAULT CHARSET=$charset",
    // note that single quotes are necessary on the data below due to the dollar-signs
    "INSERT INTO `{$prefix}users` (`id`, `email`, `username`, `password`, `fname`, `lname`, `admin`, `permissions`, `logins`, `account_owner`, `account_id`, `company`, `stripe_cust_id`, `billing_phone`, `billing_srt1`, `billing_srt2`, `billing_city`, `billing_state`, `billing_zip_code`, `timezone_string`, `join_date`, `last_login`, `email_verified`, `vericode`, `title`, `active`, `bio`, `google_uid`, `facebook_uid`) VALUES ".
       '(1, "userspicephp@gmail.com", "admin", "$2y$12$iE87plmPoyV1rjoZPZENLOi55frC3HrQAz70VI/ud.mzbco2wz/1S", "Admin", "User", 1, 1, 319, 1, 0, "UserSpice", "", "", "", "", "", "", "", "America/Toronto", "2016-01-01 00:00:00", "2016-12-31 13:40:06", 1, "322418", "", 0, "&lt;p&gt;This is the admin user default bio&lt;/p&gt;", "", ""),
        (2, "noreply@userspice.com", "user", "$2y$12$HZa0/d7evKvuHO8I3U8Ff.pOjJqsGTZqlX8qURratzP./EvWetbkK", "user2", "user", 0, 1, 18, 1, 0, "none", "", "", "", "", "", "", "", "Europe/Tirane", "2016-01-02 00:00:00", "2016-10-27 07:15:39", 1, "970748", "", 1, "&lt;p&gt;This is the user user bio&lt;/p&gt;", "", "")',
    "CREATE TABLE `{$prefix}users_online` (
          `id` int(10) NOT NULL,
          `ip` varchar(15) NOT NULL,
          `timestamp` varchar(15) NOT NULL,
          `user_id` int(10) NOT NULL,
          `session` varchar(50) NOT NULL
        ) ENGINE=$engine DEFAULT CHARSET=$charset",
    "CREATE TABLE `{$prefix}users_session` (
          `id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `hash` varchar(255) NOT NULL,
          `uagent` text
        ) ENGINE=$engine DEFAULT CHARSET=$charset",
    #"DROP VIEW IF EXISTS `{$prefix}groups_groups`",
    #"CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `{$prefix}groups_groups`  AS  select ((`groups`.`id` * 10000) + `groups`.`id`) AS `id`,`groups`.`id` AS `parent_id`,`groups`.`id` AS `child_id` from `{$prefix}groups` `groups` union select ((`ug1`.`group_id` * 10000) + `ug1`.`user_id`) AS `id`,`ug1`.`group_id` AS `parent_id`,`ug1`.`user_id` AS `child_id` from `{$prefix}groups_users_raw` `ug1` where (`ug1`.`user_is_group` = 1) union select ((`ug1`.`group_id` * 10000) + `ug2`.`user_id`) AS `id`,`ug1`.`group_id` AS `parent_id`,`ug2`.`user_id` AS `child_id` from (`{$prefix}groups_users_raw` `ug1` join `{$prefix}groups_users_raw` `ug2` on((`ug1`.`user_id` = `ug2`.`group_id`))) where ((`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 1)) union select ((`ug1`.`group_id` * 10000) + `ug3`.`user_id`) AS `id`,`ug1`.`group_id` AS `group_id`,`ug3`.`user_id` AS `user_id` from ((`{$prefix}groups_users_raw` `ug1` join `{$prefix}groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `{$prefix}groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) where ((`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 1)) union select ((`ug1`.`group_id` * 10000) + `ug4`.`user_id`) AS `id`,`ug1`.`group_id` AS `group_id`,`ug4`.`user_id` AS `user_id` from (((`{$prefix}groups_users_raw` `ug1` join `{$prefix}groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `{$prefix}groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) join `{$prefix}groups_users_raw` `ug4` on((`ug3`.`group_id` = `ug4`.`user_id`))) where ((`ug4`.`user_is_group` = 1) and (`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 1))",
    "DROP VIEW IF EXISTS `{$prefix}groups_users`",
    "CREATE VIEW `{$prefix}groups_users`  AS  select `{$prefix}groups_users_raw`.`id` AS `id`,`{$prefix}groups_users_raw`.`user_id` AS `user_id`,`{$prefix}groups_users_raw`.`group_id` AS `group_id`,0 AS `nested` from `{$prefix}groups_users_raw` where (`{$prefix}groups_users_raw`.`user_is_group` = 0) union select (`ug1`.`user_id` + (`ug2`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug2`.`group_id` AS `group_id`,1 AS `nested` from (`{$prefix}groups_users_raw` `ug1` join `{$prefix}groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) where ((`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) union select (`ug1`.`user_id` + (`ug3`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug3`.`group_id` AS `group_id`,1 AS `nested` from ((`{$prefix}groups_users_raw` `ug1` join `{$prefix}groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `{$prefix}groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) where ((`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) union select (`ug1`.`user_id` + (`ug4`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug4`.`group_id` AS `group_id`,1 AS `nested` from (((`{$prefix}groups_users_raw` `ug1` join `{$prefix}groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `{$prefix}groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) join `{$prefix}groups_users_raw` `ug4` on((`ug3`.`group_id` = `ug4`.`user_id`))) where ((`ug4`.`user_is_group` = 1) and (`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) union select (`ug1`.`user_id` + (`ug5`.`group_id` * 10000)) AS `id`,`ug1`.`user_id` AS `user_id`,`ug5`.`group_id` AS `group_id`,1 AS `nested` from ((((`{$prefix}groups_users_raw` `ug1` join `{$prefix}groups_users_raw` `ug2` on((`ug1`.`group_id` = `ug2`.`user_id`))) join `{$prefix}groups_users_raw` `ug3` on((`ug2`.`group_id` = `ug3`.`user_id`))) join `{$prefix}groups_users_raw` `ug4` on((`ug3`.`group_id` = `ug4`.`user_id`))) join `{$prefix}groups_users_raw` `ug5` on((`ug4`.`group_id` = `ug5`.`user_id`))) where ((`ug5`.`user_is_group` = 1) and (`ug4`.`user_is_group` = 1) and (`ug3`.`user_is_group` = 1) and (`ug2`.`user_is_group` = 1) and (`ug1`.`user_is_group` = 0)) ",
    "ALTER TABLE `{$prefix}field_defs`
          ADD PRIMARY KEY (`id`),
          ADD UNIQUE KEY `name` (`name`)",
    "ALTER TABLE `{$prefix}groups`
          ADD PRIMARY KEY (`id`)",
    "ALTER TABLE `{$prefix}groups_menus`
          ADD PRIMARY KEY (`id`),
          ADD KEY `group_id` (`group_id`),
          ADD KEY `menu_id` (`menu_id`)",
    "ALTER TABLE `{$prefix}groups_pages`
          ADD PRIMARY KEY (`id`),
          ADD UNIQUE KEY `group_id_2` (`group_id`,`page_id`),
          ADD KEY `group_id` (`group_id`),
          ADD KEY `page_id` (`page_id`),
          ADD KEY `page_id_2` (`page_id`),
          ADD KEY `auth` (`auth`)",
    "ALTER TABLE `{$prefix}groups_roles_users`
          ADD PRIMARY KEY (`id`),
          ADD KEY `group_id` (`group_id`),
          ADD KEY `role_id` (`role_group_id`),
          ADD KEY `user_id` (`user_id`)",
    "ALTER TABLE `{$prefix}groups_users_raw`
          ADD PRIMARY KEY (`id`),
          ADD UNIQUE KEY `group_id_2` (`group_id`,`user_id`,`user_is_group`),
          ADD KEY `user_id` (`user_id`),
          ADD KEY `group_id` (`group_id`),
          ADD KEY `user_is_group` (`user_is_group`)",
    "ALTER TABLE `{$prefix}grouptypes`
          ADD PRIMARY KEY (`id`)",
    "ALTER TABLE `{$prefix}menus`
          ADD PRIMARY KEY (`id`)",
    "ALTER TABLE `{$prefix}pages`
          ADD PRIMARY KEY (`id`)",
    "ALTER TABLE `{$prefix}profiles`
          ADD PRIMARY KEY (`id`)",
    "ALTER TABLE `{$prefix}settings`
          ADD PRIMARY KEY (`id`)",
    "ALTER TABLE `{$prefix}users`
          ADD PRIMARY KEY (`id`),
          ADD KEY `EMAIL` (`email`) USING BTREE",
    "ALTER TABLE `{$prefix}users_online`
          ADD PRIMARY KEY (`id`)",
    "ALTER TABLE `{$prefix}users_session`
          ADD PRIMARY KEY (`id`)",
    "ALTER TABLE `{$prefix}field_defs`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14",
    "ALTER TABLE `{$prefix}groups`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59",
    "ALTER TABLE `{$prefix}groups_menus`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44",
    "ALTER TABLE `{$prefix}groups_pages`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61",
    "ALTER TABLE `{$prefix}groups_roles_users`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14",
    "ALTER TABLE `{$prefix}groups_users_raw`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167",
    "ALTER TABLE `{$prefix}grouptypes`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33",
    "ALTER TABLE `{$prefix}menus`
          MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42",
    "ALTER TABLE `{$prefix}pages`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98",
    "ALTER TABLE `{$prefix}profiles`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5",
    "ALTER TABLE `{$prefix}settings`
          MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2",
    "ALTER TABLE `{$prefix}users`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7",
    "ALTER TABLE `{$prefix}users_online`
          MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10",
    "ALTER TABLE `{$prefix}users_session`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3",
];

# If you update this, please be sure to update $us_tables initialization in
# users/core/includes/init.php as well.
$us_tables=['field_defs', 'groups', 'groups_menus', 'groups_pages', 'groups_roles_users',
    'groups_users', 'groups_users_raw', 'grouptypes', 'menus', 'pages', 'profiles',
    'settings', 'users', 'users_online', 'users_session', ];

$db = DB::getInstance();
$existing_tables = [];
foreach ($us_tables as $t) {
    $sql = "SELECT * FROM {$prefix}$t WHERE 1=2";
    if (!$db->query($sql)->error()) {
        $existing_tables[] = $t;
    }
}
if (@$_POST['save']) {
    if ($existing_tables) {
        foreach ($existing_tables as $t) {
            $sql = "DROP TABLE {$prefix}$t";
            $db->query($sql);
        }
    }
    $errcount=0;
    foreach ($init_commands as $sql) {
        $db->query($sql);
        if ($e = $db->error()) {
            var_dump($e);
            echo "SQL PROBLEM: $sql<br />\n";
            $errcount++;
        }
    }
    if ($errcount) {
        echo "Completed with $errcount errors. Installation was NOT successful.<br />\n";
    } else {
        echo "Database initialized successfully. Theoretically you are good to go!";
    }
} else {
    if ($existing_tables) {
        $continue_msg = "<h2>These tables already exist in the database:</h2><ul>\n";
        foreach ($existing_tables as $t) {
            $continue_msg .= "<li>$t";
            if ($prefix) {
                $continue_msg .= " ({$prefix}$t with prefix)";
            }
            $continue_msg .= "</li>\n";
        }
        $continue_msg .= "</ul>\n";
        $button_label = 'DELETE and Install';
        $continue_msg .= "<h2>Press '$button_label' to delete (drop) these tables and all data in them and initialize the database from scratch. ALL EXISTING DATA WILL BE LOST. There is no undo on this action!</h2>\n";
    } else {
        $button_label = 'Continue';
        $continue_msg = "<h2>Everything looks good. No tables will be overwritten in the database. Press '$button_label' to initialize the database.</h2>\n";
    }
?>
    <html><head></head>
    <body>
        <?= $continue_msg ?>
        <br />
        <form method="post">
            <input type="submit" name="save" value="<?= $button_label ?>" />
        </form>
    </body>
    </html>
<?php
}
