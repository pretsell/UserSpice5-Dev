<?php
#include_once("../users/core/include/init.php");

/*
 * This form facilitates creating US_ROOT_DIR/local/config.php during UserSpice installation
 * A typical config.php file might look like this:

<?php

$cfg = new Config(
    array(
        'mysql' => array(
            'host'      => 'localhost',
            'username'  => 'us_user',
            'password'  => 'qwer789',
            'db'        => 'us5',
            'prefix'    => 'us_',
            # If this is NOT a live, production system you can uncomment the following lines
            # to enable display of SQL errors. Note that this presents a potential Security
            # risk and therefore should not be used in live, production environments.
            'options'	=> array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode = ''",
            					 PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING),
        ),
        'remember' => array(
            'cookie_name'   => 'pmqesoxiw318374csb',
            'cookie_expiry' => 604800  //One week, feel free to make it longer
        ),
        'session' => array(
            'session_name' => 'user',
            'token_name' => 'token',
        ),
        #'site_language' => 'english',
        #'us_script_path' => array('local/', 'core/'),
        $forms_path = [/mywebapp/forms/',
                       US_ROOT_DIR.'local/forms/',
                       US_ROOT_DIR.'core/forms/',
                       # uncomment the line below to enable tutorial forms
                       #US_ROOT_DIR.'tutorial/forms/',
        ];
        #'us_page_path' = US_ROOT_DIR,
        // page_paths should start from DOCUMENT_ROOT, starting with /
        'page_paths' => [US_URL_ROOT],
    )
);

 */

require_once('init.php');

if (@$_POST['save']) {
    $mysqlHost = $_POST['mysql_host'];
    $mysqlUser = $_POST['mysql_user'];
    $mysqlPass = $_POST['mysql_pass'];
    $mysqlDb = $_POST['mysql_db'];
    $mysqlPrefix = $_POST['mysql_prefix'];
    $cookieName = $_POST['cookie_name'];
    $cookieExpiry = $_POST['cookie_expiry'];
    $sessionName = $_POST['session_name'];
    $tokenName = $_POST['token_name'];
    $alt_dev_path = $_POST['alt_dev_path'];
} elseif (isset($cfg)) {
    # config.php already exists - load existing values to allow modification
    $mysqlHost = $cfg->simpleGet('mysql/host');
    $mysqlUser = $cfg->simpleGet('mysql/username');
    $mysqlPass = $cfg->simpleGet('mysql/password');
    $mysqlDb = $cfg->simpleGet('mysql/db');
    $mysqlPrefix = $cfg->simpleGet('mysql/prefix');
    $cookieName = $cfg->simpleGet('remember/cookie_name');
    $cookieExpiry = $cfg->simpleGet('remember/cookie_expiry');
    $sessionName = $cfg->simpleGet('session/session_name');
    $tokenName = $cfg->simpleGet('session/token_name');
    $alt_dev_path = $cfg->simpleGet('alt_dev_path');
} else {
    # appropriate defaults
    $mysqlHost = 'localhost';
    $mysqlUser = $mysqlPass = $mysqlDb = $mysqlPrefix = '';
    $cookieName = 'pmqesoxiw318374csb';
    $cookieExpiry = 604800;  //One week
    $sessionName = 'user';
    $tokenName = 'token';
    $alt_dev_path = '';
}
$cfgPath = US_ROOT_DIR . "local/config.php";
# $alt_dev_path/forms is where 3rd party developers want to put their
# simplified forms (assuming they want to use master_form.php)
if ($alt_dev_path) {
    $tmp_form_path = "'" . $alt_dev_path . "/forms/',";
} else {
    $tmp_form_path = '';
}
$forms_path = "$tmp_form_path
                         US_ROOT_DIR.'local/forms/',
                         US_ROOT_DIR.'core/forms/',
                         # uncomment the line below to enable tutorial forms
                         #US_ROOT_DIR.'tutorial/forms/', ";
$cfgContents = <<<EOF
<?php

/*
 * THIS FILE WAS CREATED DYNAMICALLY DURING THE INSTALLATION PROCESS.
 *
 * These configuration values may be edited at any time. They will not be
 * overwritten by upgrades to the system. Note that settings from the
 * database table `settings` will override these (silently) if you try to
 * set values here that correspond to the name of columns in that table.
 */
\$cfg = new Config(
    array(
        'mysql' => array(
            'host'      => '$mysqlHost',
            'username'  => '$mysqlUser',
            'password'  => '$mysqlPass',
            'db'        => '$mysqlDb',
            'prefix'    => '$mysqlPrefix',
            # If this is NOT a live, production system you can uncomment the following lines
            # to enable display of SQL errors. Note that this presents a potential Security
            # risk and therefore should not be used in live, production environments.
            'options'	=> array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode = ''",
								 #PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
                             ),
        ),
        'remember' => array(
            'cookie_name'   => '$cookieName',
            'cookie_expiry' => $cookieExpiry  //604800=One week, feel free to change as desired
        ),
        'session' => array(
            'session_name' => '$sessionName',
            'token_name' => '$tokenName',
        ),
        #'site_language' => 'english',
        #'us_script_path' => array('local/', 'core/'),
        'alt_dev_path' => '$alt_dev_path',
        'forms_path' => [$forms_path],
        #'us_page_path' = US_ROOT_DIR,
        // page_paths should start from US_DOC_ROOT, starting with /
        'page_paths' => [US_URL_ROOT],
    )
);
EOF;

if (@$_POST['save']) {
    $errors = [];
    /*
     * Check database credentials
     */
    # THIS IS NOT DONE...
    dbg("Not checking DB credentials yet");
    # Check if our database credentials are valid or not and set $errors appropriately...

    if (file_exists($cfgPath) && !@$_POST['overwrite_cfgpath']) {
        $errors['overwrite_cfgpath'] = "$cfgPath already exists. Please confirm overwrite.";
    }
    if (!$errors) {
        $cfgDir = dirname($cfgPath);
        if (!file_exists($cfgDir)) {
            if (!mkdir($cfgDir, 0777, true)) {
                $errors['cfgdir'] = "ERROR: Directory '$cfgDir' (for config.php) does not exist and cannot be created. Please resolve before continuing.<br />\n";
            }
        }
        if (file_put_contents($cfgPath, $cfgContents) === false) {
            $errors['cfgpath'] = "ERROR creating '$cfgPath'. Please resolve before continuing.<br />\n";
        } else {
            echo "Created '$cfgPath' successfully<br />\n";
        }
    }
    if (!$errors) {
        Redirect::to('mkpages.php');
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
.labels { text-align: right; vertical-align: top; }
.input_cell {}
.text_input { width: 30em; }
.errors { color: red; font-weight: bold; }
</style>
</head>
<body>
    <h2>Step X - Create config.php</h2>
    <form method="POST">
        <table border=1>
            <tr>
                <td class="labels">MYSQL Host:</td>
                <td class="text_input"> <input type="text" name="mysql_host" value="<?= $mysqlHost ?>" class="text_input"/></td>
                <td class="errors"><?= @$errors['mysql_host'] ?></td>
            </tr>
            <tr>
                <td class="labels">MYSQL Username:</td>
                <td class="text_input"> <input type="text" name="mysql_user" value="<?= $mysqlUser ?>" class="text_input"/></td>
                <td class="errors"><?= @$errors['mysql_user'] ?></td>
            </tr>
            <tr>
                <td class="labels">MYSQL Password:</td>
                <td class="input_cell"> <input type="text" name="mysql_pass" value="<?= $mysqlPass ?>" class="text_input"/></td>
                <td class="errors"><?= @$errors['mysql_pass'] ?></td>
            </tr>
            <tr>
                <td class="labels">MYSQL Database Name:</td>
                <td class="input_cell"> <input type="text" name="mysql_db" value="<?= $mysqlDb ?>" class="text_input"/></td>
                <td class="errors"><?= @$errors['mysql_db'] ?></td>
            </tr>
            <tr>
                <td class="labels">MYSQL Table Prefix (optional):</td>
                <td class="input_cell"> <input type="text" name="mysql_prefix" value="<?= $mysqlPrefix ?>" class="text_input"/></td>
                <td class="errors"><?= @$errors['mysql_prefix'] ?></td>
            </tr>
            <tr>
                <td class="labels">Remember Me Cookie Name:</td>
                <td class="input_cell"> <input type="text" name="cookie_name" value="<?= $cookieName ?>" class="text_input"/></td>
                <td class="errors"><?= @$errors['cookie_name'] ?></td>
            </tr>
            <tr>
                <td class="labels">Remember Me Cookie Expiration:</td>
                <td class="input_cell"> <input type="text" name="cookie_expiry" value="<?= $cookieExpiry ?>" class="text_input"/></td>
                <td class="errors"><?= @$errors['cookie_expiry'] ?></td>
            </tr>
            <tr>
                <td class="labels">Session Name:</td>
                <td class="input_cell"> <input type="text" name="session_name" value="<?= $sessionName ?>" class="text_input"/></td>
                <td class="errors"><?= @$errors['session_name'] ?></td>
            </tr>
            <tr>
                <td class="labels">Session Token Name:</td>
                <td class="input_cell"> <input type="text" name="token_name" value="<?= $tokenName ?>" class="text_input"/></td>
                <td class="errors"><?= @$errors['token_name'] ?></td>
            </tr>
            <tr>
                <td class="labels">Path location for YOUR web-site development (contains forms/, language/, etc.):</td>
                <td class="input_cell"> <input type="text" name="alt_dev_path" value="<?= $alt_dev_path ?>" class="text_input" /></td>
                <td class="errors"><?= @$errors['alt_dev_path'] ?></td>
            </tr>

            <tr>
                <td class="labels">Path for "config.php":</td>
                <td class="input_cell"> <input type="text" name="cfgpath" value="<?= $cfgPath ?>" class="text_input" disabled /><br />(based on US_ROOT_DIR) </td>
                <td class="errors"><?= @$errors['cfgpath'] ?><br /><?= @$errors['cfgdir'] ?></td>
            </tr>
            <?php if (file_exists($cfgPath)) { ?>
            <tr>
                <td class="labels">File "config.php" exists:</td>
                <td class="input_cell">
                    <label>
                    <input type="checkbox" name="overwrite_cfgpath" value="1" <?php if (@$_POST['overwrite_cfgpath']) { echo "checked='checked'"; } ?> class="checkbox_input" />
                    Overwrite?
                </label><br />(required)
                </td>
                <td class="errors"><?= @$errors['overwrite_cfgpath'] ?></td>
            </tr>
            <?php } ?>
        </table>
        <input type="submit" name="save" value="Create 'config.php' and go to NEXT step" />
    </form>
</body>
</html>
