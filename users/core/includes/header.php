<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title><?=configGet('site_name');?></title>

	<!-- Bootstrap Core CSS -->
	<link href="<?= US_URL_ROOT.configGet('css1') ?>" rel="stylesheet">

	<!-- Template CSS -->
	<link href="<?= US_URL_ROOT.configGet('css2') ?>" rel="stylesheet">

	<!-- Your Custom CSS Goes Here!-->
	<link href="<?= US_URL_ROOT.configGet('css3') ?>" rel="stylesheet">

	<!-- Custom Fonts -->
	<link href="<?= US_URL_ROOT."core/fonts/css/font-awesome.min.css" ?>" rel="stylesheet" type="text/css">
</head>

<body>
<div class="container"> <!-- Page container may be fluid or not -->
<?php
require_once pathFinder('includes/navigation.php');

/*
 * calculate & display breadcrumbs, if any
 */
global $T;
$db = DB::getInstance();
if (isset($form_uri)) {
    $uri = $form_uri;
} else {
    $uri = $_SERVER['PHP_SELF'];
}
$bc = [];
$sql = "SELECT title_token, breadcrumb_parent_page_id, page
        FROM $T[pages]
        WHERE page = ?";
$result = $db->query($sql, [$uri])->first();
if ($result) {
    $parent_id = $result->breadcrumb_parent_page_id;
    $sql = "SELECT title_token, breadcrumb_parent_page_id, page
            FROM $T[pages]
            WHERE id = ?";
    do {
        $bc[] = [
            'title' => lang($result->title_token),
            'page' => $result->page
        ];
        $result = $db->query($sql, [$parent_id])->first();
        if ($result) {
            $parent_id = $result->breadcrumb_parent_page_id;
        } else {
            $parent_id = null;
        }
    } while ($parent_id);
    if ($result) {
        $bc[] = [
            'title' => lang($result->title_token),
            'page' => $result->page
        ];
    }
} else {
    $parent_id = null;
}
//var_dump($bc);
$out = '';
$k = false;
foreach ($bc as $k=>$x) {
    if ($k) {
        $out = "<a href=\"$x[page]\">$x[title]</a>&nbsp;>>&nbsp;" . $out;
    } else {
        $out = $x['title']; // current page is not a link
    }
}
if ($k && $out) {
    echo $out."<br />\n";
}
