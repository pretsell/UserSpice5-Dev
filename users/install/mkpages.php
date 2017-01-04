<?php
#include_once("../users/us_core/include/init.php");
include_once('../src/z_us_root.php');
echo US_ROOT_DIR.'us_core/forms/*.php';
$us_pages = glob(US_ROOT_DIR.'us_core/forms/*.php');
foreach ($us_pages as $p) {
    $p = basename($p);
    $contents = <<<EOF
<?php
\$formName = '$p';
#\$enableMasterHeaders = \$enableMasterFooters = true;
require_once $z_us_root_path.'z_us_root.php';
require_once US_ROOT_DIR.'us_core/master_form.php';
EOF;
    if (file_put_contents(US_ROOT_DIR.$p, $contents) === false) {
        echo "ERROR creating $p<br />\n";
    } else {
        echo "Created $p<br />\n";
    }
}

$contents = <<<EOF
<?php //DO NOT DELETE THIS FILE.
define("US_DOC_ROOT", '$_SERVER[DOC_ROOT]');
define("US_URL_ROOT", 'Some/relative/path/from/install/page.php');
define('US_ROOT_DIR', '/absolute/path/to/users/with/trailing/slash');
EOF;

if (file_put_contents(US_ROOT_DIR.'z_us_root.php', $contents) === false) {
    echo "ERROR creating z_us_root.php<br />\n";
} else {
    echo "Created z_us_root.php<br />\n";
}
