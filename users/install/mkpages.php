<?php
#include_once("../users/us_core/include/init.php");

#
# Start by creating z_us_root.php
#

if (@$_POST['create_files']) {
    $docRoot = $_POST['us_doc_root'];
    $rootDir = $_POST['us_root_dir'];
    $urlRoot = $_POST['us_url_root'];
} else {
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $rootDir = dirname(dirname(__FILE__)); // double dirname() points to ..
    $urlRoot = str_replace($docRoot, '', $rootDir);
}
$docRoot = trim(str_replace('\\', '/', $docRoot)); // use forward slashes
$rootDir = trim(str_replace('\\', '/', $rootDir)); // use forward slashes
$urlRoot = trim(str_replace('\\', '/', $urlRoot)); // use forward slashes
if (substr($docRoot, -1, 1) == '/') {
    $docRoot = substr($docRoot, 0, -1); // US_DOC_ROOT needs to NOT have a trailing slash
}
if (substr($rootDir, -1, 1) != '/') {
    $rootDir .= '/'; // US_ROOT_DIR needs trailing slash
}
if (substr($urlRoot, -1, 1) != '/') {
    $urlRoot .= '/'; // US_URL_ROOT needs trailing slash
}
$zPath = "${rootDir}/z_us_root.php";
$zContents = <<<EOF
<?php //DO NOT DELETE THIS FILE.
define("US_DOC_ROOT", '$docRoot');
define('US_ROOT_DIR', '$rootDir');
define("US_URL_ROOT", '$urlRoot');
EOF;

if ($_POST['create_files']) {
    $errors = [];
    if ($_POST['us_doc_root'] == $docRoot) {
        $errors['us_doc_root'] = "Please reconfirm changed value (was '$_POST[us_doc_root]')";
    }
    if ($_POST['us_root_dir'] == $rootDir) {
        $errors['us_root_dir'] = "Please reconfirm changed value (was '$_POST[us_root_dir]')";
    }
    if ($_POST['us_url_root'] == $urlRoot) {
        $errors['us_url_root'] = "Please reconfirm changed value (was '$_POST[us_url_root]')";
    }
    if (file_exists($zPath) && !@$_POST['overwrite_z_path']) {
        $errors['overwrite_z_path'] = "$zPath already exists. Please confirm overwrite.";
    }
    if (!$errors) {
        $zDir = dirname($zPath);
        if (!file_exists($zDir)) {
            if (!mkdir($zDir, 0777, true)) {
                $errors['zdir'] = "ERROR: Directory '$zDir' (for z_us_root.php) does not exist and cannot be created. Please resolve before continuing.<br />\n";
            }
        }
        if (file_put_contents($zPath, $contents) === false) {
            $errors['zpath'] = "ERROR creating '$zPath'. Please resolve before continuing.<br />\n";
        } else {
            echo "Created '$zPath' successfully<br />\n";
        }
    }
    if (!$errors) {
        # pull in those definitions
        require_once('../z_us_root.php');
        # Make sure US_ROOT_DIR exists as dir
        if (!file_exists(US_ROOT_DIR)) {
            if (!mkdir(US_ROOT_DIR, 0777, true)) {
                $errors['us_root_dir'] = 'ERROR: Cannot create directory "'.US_ROOT_DIR.'"';
            }
        }
    }
    $creates = 0;
    if (!$errors) {
        #
        # Now create the page stubs for each form in US_ROOT_DIR/us_core/forms/
        #
        echo US_ROOT_DIR.'/us_core/forms/*.php'."<br />\n";
        $us_pages = glob(US_ROOT_DIR.'/us_core/forms/*.php');
        foreach ($us_pages as $p) {
            echo "Creating $p...<br />\n";
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
                $errors['pages'][] = $p;
            } else {
                echo "Created $p<br />\n";
                $creates++;
            }
        }
    }
    if (!$errors && $creates) {
        redirect(dirname(__FILE__).'/init_pages.php');
        exit;
    }
}

/*
 * Display the Form
 */
?>
<html>
<head>
<style>
.labels { text-align: right; }
.text_input {}
.errors { color: red; }
</style>
</head>
<body>
    <h2>Step X - Create Pages</h2>
    <form method="POST">
        <table>
            <tr>
                <td class="labels">Document Root:</td>
                <td class="text_input"> <input type="text" name="us_doc_root" value="<?= $docRoot ?>"/> </td>
                <td class="errors"><?= @$errors['us_doc_root'] ?></td>
            </tr>
        </table>
        <input type="submit" name="create_files" value="Create" />
    </form>
</body>
</html>
