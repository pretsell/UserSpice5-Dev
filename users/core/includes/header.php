<?php
/*
UserSpice
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
/*
 * Done with breadcrumbs
 */
