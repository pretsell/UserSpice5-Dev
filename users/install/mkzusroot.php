<?php
#include_once("../users/core/include/init.php");

/*
 * This form facilitates creating US_ROOT_DIR/z_us_root.php during UserSpice installation
 * A typical z_us_root.php file might look like this on a WAMP system:#

<?php //DO NOT DELETE THIS FILE.
define("US_DOC_ROOT", 'C:/wamp/www');
define('US_ROOT_DIR', 'C:/wamp/www/myapp/users/');
define("US_URL_ROOT", '/myapp/users/');

 */

var_dump($_POST);
if (@$_POST['save']) {
    $docRoot = $_POST['us_doc_root'];
    $rootDir = $_POST['us_root_dir'];
    $urlRoot = $_POST['us_url_root'];
} else {
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $rootDir = dirname(dirname(__FILE__)); // double dirname() points to ..
    # $urlRoot will be set below, after converting backslashes to forward slashes
}
$docRoot = trim(str_replace('\\', '/', $docRoot)); // use forward slashes
$rootDir = trim(str_replace('\\', '/', $rootDir)); // use forward slashes
if (substr($docRoot, -1, 1) == '/') {
    $docRoot = substr($docRoot, 0, -1); // US_DOC_ROOT needs to NOT have a trailing slash
}
if (substr($rootDir, -1, 1) != '/') {
    $rootDir .= '/'; // US_ROOT_DIR needs trailing slash
}
if (!@$_POST['save']) {
    $urlRoot = str_replace($docRoot, '', $rootDir);
}
$urlRoot = trim(str_replace('\\', '/', $urlRoot)); // use forward slashes
if (substr($urlRoot, -1, 1) != '/') {
    $urlRoot .= '/'; // US_URL_ROOT needs trailing slash
}
$zPath = "${rootDir}z_us_root.php";
$zContents = <<<EOF
<?php //DO NOT DELETE THIS FILE.
define("US_DOC_ROOT", '$docRoot');
define('US_ROOT_DIR', '$rootDir');
define("US_URL_ROOT", '$urlRoot');
EOF;

if (@$_POST['save']) {
    $errors = [];
    if ($_POST['us_doc_root'] != $docRoot) {
        $errors['us_doc_root'] = "Please reconfirm changed value (was '$_POST[us_doc_root]')";
    }
    if ($_POST['us_root_dir'] != $rootDir) {
        $errors['us_root_dir'] = "Please reconfirm changed value (was '$_POST[us_root_dir]')";
    }
    if ($_POST['us_url_root'] != $urlRoot) {
        $errors['us_url_root'] = "Please reconfirm changed value (was '$_POST[us_url_root]')";
    }
    if (file_exists($zPath) && !@$_POST['overwrite_zpath']) {
        $errors['overwrite_zpath'] = "$zPath already exists. Please confirm overwrite.";
    }
    if (!$errors) {
        $zDir = dirname($zPath);
        if (!file_exists($zDir)) {
            if (!mkdir($zDir, 0777, true)) {
                $errors['zdir'] = "ERROR: Directory '$zDir' (for z_us_root.php) does not exist and cannot be created. Please resolve before continuing.<br />\n";
            }
        }
        if (file_put_contents($zPath, $zContents) === false) {
            $errors['zpath'] = "ERROR creating '$zPath'. Please resolve before continuing.<br />\n";
        } else {
            echo "Created '$zPath' successfully x<br />\n";
        }
    }
    if (!$errors) {
        require_once(dirname(__FILE__).'/init.php');
        Redirect::to('mkconfig.php');
        echo "Trying to redirect, __DIR__=".__DIR__."<br />\n";
        #exit;
    }
}

/*
 * Display the Form
 */
?>
<html>
<head>
<title>Create z_us_root.php</title>
<style type="text/css">
.labels { text-align: right; vertical-align: top; }
.input_cell {}
.text_input { width: 30em; }
.errors { color: red; font-weight: bold; }
</style>
</head>
<body>
    <h2>Step X - Create z_us_root.php</h2>
    <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
        <table border=1>
            <tr>
                <td class="labels">Document Root:</td>
                <td class="text_input"> <input type="text" name="us_doc_root" value="<?= $docRoot ?>" class="text_input"/><br />(US_DOC_ROOT) </td>
                <td class="errors"><?= @$errors['us_doc_root'] ?></td>
            </tr>
            <tr>
                <td class="labels">UserSpice Root:</td>
                <td class="text_input"> <input type="text" name="us_root_dir" value="<?= $rootDir ?>" class="text_input"/><br />(US_ROOT_DIR) </td>
                <td class="errors"><?= @$errors['us_root_dir'] ?></td>
            </tr>
            <tr>
                <td class="labels">UserSpice URL Root:</td>
                <td class="input_cell"> <input type="text" name="us_url_root" value="<?= $urlRoot ?>" class="text_input"/><br />(US_URL_ROOT) </td>
                <td class="errors"><?= @$errors['us_url_root'] ?></td>
            </tr>
            <tr>
                <td class="labels">Path for "z_us_root.php":</td>
                <td class="input_cell"> <input type="text" name="zpath" value="<?= $zPath ?>" class="text_input" disabled /><br />(based on US_ROOT_DIR) </td>
                <td class="errors"><?= @$errors['zpath'] ?><br /><?= @$errors['zdir'] ?></td>
            </tr>
            <?php if (file_exists($zPath)) { ?>
            <tr>
                <td class="labels">File "z_us_root.php" exists:</td>
                <td class="input_cell">
                    <labelx>
                    <input type="checkbox" name="overwrite_zpath" value="overwrite" <?php if (@$_POST['overwrite_zpath']) { echo "checked"; } ?> class="checkbox_input" />
                    Overwrite?
                </labelx><br />(required)
                </td>
                <td class="errors"><?= @$errors['overwrite_zpath'] ?></td>
            </tr>
            <?php } ?>
        </table>
        <input type="submit" name="save" value="Create 'z_us_root.php' and go to NEXT step" />
    </form>
</body>
</html>
