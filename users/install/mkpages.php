<?php

require_once('init.php');
if (@$_POST['save']) {
    $errors = [];
    $creates = 0;
    $pagePath = $_POST['page_path'];
    $pagePath = str_replace('\\', '/', $pagePath); // convert to forward slashes
    if (substr($pagePath, -1, 1) != '/') {
        $pagePath = $pagePath . '/'; // add trailing slash
    }
    if (strpos(':', $pagePath) !== false || strpos(';', $pagePath) !== false) {
        $errors['page_path'] = 'No colons or semi-colons are allowed. This must be a single path under US_DOC_ROOT.';
    } else {
        if ($pagePath != $_POST['page_path']) {
            $errors['page_path'] = 'Directory was modified. Please confirm.';
        }
    }
    #
    # Now create the page stubs for each form in US_ROOT_DIR/us_core/forms/
    #
    if (!$errors) {
        echo "Checking for files in '".US_ROOT_DIR."/us_core/forms/*.php'<br />\n";
        $rootDir = US_ROOT_DIR; // easier to use in heredocs than the constant
        $us_pages = glob(US_ROOT_DIR.'/us_core/forms/*.php');
        foreach ($us_pages as $p) {
            #echo "Creating $p...<br />\n";
            $p = basename($p);
            $contents = <<<EOF
<?php
\$formName = '$p';
#\$enableMasterHeaders = \$enableMasterFooters = true;
require_once '{$rootDir}z_us_root.php';
require_once US_ROOT_DIR.'us_core/master_form.php';
EOF;
            if (file_put_contents(US_DOC_ROOT.$pagePath.$p, $contents) === false) {
                #echo "ERROR creating $p<br />\n";
                $errors['pages'][] = $p;
            } else {
                #echo "Created $p<br />\n";
                $creates++;
            }
        }
    }
    if (!$errors && $creates) {
        $pages_tbl = $cfg->simpleGet('mysql/prefix');
        $sql = "UPDATE $pages_tbl SET page = REPLACE(page, 'US_PAGE_PATH/', '$pagePath/') WHERE 1";
        $db = DB::getInstance();
        $db->query($sql);
    }
    if (!$errors && $creates) {
        Redirect::to('initdb.php');
        exit;
    }
} else {
    # default values for form
    $pagePath = US_URL_ROOT;
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
    <h2>Step X - Create Pages</h2>
    <form method="POST">
        <table border=1>
            <tr>
                <td class="labels">Directory in which pages will be created:</td>
                <td class="text_input"> <input type="text" name="page_path" value="<?= $pagePath ?>" class="text_input"/><br />(under US_DOC_ROOT) </td>
                <td class="errors"><?= @$errors['page_path'] ?></td>
            </tr>
        </table>
        <input type="submit" name="save" value="Create Pages and go to NEXT step" />
    </form>
</body>
</html>
