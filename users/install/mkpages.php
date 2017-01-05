<?php
#include_once("../users/us_core/include/init.php");

#
# Start by creating z_us_root.php
#

# We assume that this is being run from US_ROOT_DIR/install so we find z_us_rootphp in ../
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$rootDir = str_replace('\\', '/', dirname(dirname(__FILE__)));
$urlRoot = str_replace($docRoot, '', $rootDir);
if (substr($rootDir, -1, 1) != '/') {
    $rootDir .= '/';
}
if (substr($urlRoot, -1, 1) != '/') {
    $urlRoot .= '/';
}
$contents = <<<EOF
<?php //DO NOT DELETE THIS FILE.
define("US_DOC_ROOT", '$docRoot');
define('US_ROOT_DIR', '$rootDir');
define("US_URL_ROOT", '$urlRoot');
EOF;

if (file_put_contents('../z_us_root.php', $contents) === false) {
    echo "ERROR creating z_us_root.php<br />\n";
    exit;
} else {
    echo "Created z_us_root.php<br />\n";
}

# Now pull in those definitions
include_once('../z_us_root.php');

#
# Now create the page stubs for each form in forms/
# 
echo US_ROOT_DIR.'/us_core/forms/*.php'."<br />\n";
$us_pages = glob(US_ROOT_DIR.'/us_core/forms/*.php');
$us_pages[] = 'admin_role.php';
$us_pages[] = 'admin_roles.php';
foreach ($us_pages as $p) {
    echo "Working on $p<br />\n";
    $p = basename($p);
    $contents = <<<EOF
<?php
\$formName = '$p';
#\$enableMasterHeaders = \$enableMasterFooters = true;
require_once '{$rootDir}z_us_root.php';
require_once US_ROOT_DIR.'us_core/master_form.php';
EOF;
    if (file_put_contents(US_ROOT_DIR.$p, $contents) === false) {
        echo "ERROR creating $p<br />\n";
    } else {
        echo "Created $p<br />\n";
    }
}

/*
 * pages.page should contain a link to the contents of $_SERVER['PHP_SELF']
 * menus.page_id should contain pages.id for the appropriate row
 *
dbg('THIS IS A VERY INCOMPLETE LIST OF PAGES');
$pages = ['admin_group.php', 'admin_groups.php', 'admin_role.php', 'admin_roles.php'];
foreach ($pages as $page) {
    # Insert US_URL_ROOT.$page INTO $T[pages]
    # Now figure out menus and make sure the pages.id line up
}
*/
