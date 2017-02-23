<?php
/* init_pages.php
 * This is part of the installation procedures for UserSpice
 * This script initializes the `pages` table
 */

/*
 * pages.page should contain a link to the contents of $_SERVER['PHP_SELF']
 * menus.page_id should contain pages.id for the appropriate row
 *
dbg('THIS IS A VERY INCOMPLETE LIST OF PAGES');
foreach ($pages as $page) {
    # Insert US_URL_ROOT.$page INTO $T[pages]
    # Now figure out menus and make sure the pages.id line up
}
*/
require_once('../z_us_root.php');
$pages = [
    'admin_group.php' =>  ['parent' => 'admin.php', 'id' => 11],
    'admin_groups.php' => ['parent' => 'admin_group.php', 'id' => 12],
    'admin_role.php' =>   ['parent' => 'admin.php', 'id' => 13],
    'admin_roles.php' =>  ['parent' => 'admin_role.php', 'id' => 14],
];
