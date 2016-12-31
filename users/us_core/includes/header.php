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
	<link href="<?= US_URL_ROOT."us_core/fonts/css/font-awesome.min.css" ?>" rel="stylesheet" type="text/css">
</head>

<body>
<div class="container"> <!-- Page container may be fluid or not -->
<?php require_once pathFinder('includes/navigation.php'); ?>
