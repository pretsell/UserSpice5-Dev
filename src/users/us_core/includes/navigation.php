<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

/*
Load main navigation menus
*/
$main_nav_all = $GLOBALS['db']->query("SELECT * FROM menus WHERE menu_title='main' ORDER BY display_order");

/*
Set "results" to true to return associative array instead of object...part of db class
*/
$main_nav=$main_nav_all->results(true);

/*
Make menu tree
*/
$prep=prepareMenuTree($main_nav);

if (file_exists(US_ROOT_DIR.'local/images/logo.png')) {
    $logo = US_URL_ROOT."/local/images/logo.png";
} else {
    $logo = US_URL_ROOT."/us_core/images/logo.png";
}
?>

<nav class="navbar navbar-default">
<div class="container-fluid">
  <div class="navbar-header">
	<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar_test" aria-expanded="false" aria-controls="navbar">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	</button>
	<a href="<?=US_URL_ROOT?>"><img src="<?= $logo ?>"></img></a>
  </div>
  <div id="navbar_test" class="navbar-collapse collapse">
	<ul class="nav navbar-nav navbar-right">
<?php
foreach ($prep as $key => $value) {
	/*
	Check if there are children of the current nav item...if no children, display single menu item, if children display dropdown menu
	*/
	if (sizeof($value['children'])==0) {
		if ($GLOBALS['user']->isLoggedIn()) {
			if (checkMenu($value['id'],$GLOBALS['user']->data()->id) && $value['logged_in']==1) {
				echo prepareItemString($value);
			}
		} else {
			if ($value['logged_in']==0 || checkMenu($value['id'])) {
				echo prepareItemString($value);
			}
		}
	} else {
		if ($GLOBALS['user']->isLoggedIn()) {
			if (checkMenu($value['id'],$GLOBALS['user']->data()->id) && $value['logged_in']==1) {
				$dropdownString=prepareDropdownString($value);
				$dropdownString=str_replace('{{username}}',$GLOBALS['user']->data()->username,$dropdownString);
				echo $dropdownString;
			}
		} else {
			if ($value['logged_in']==0 || checkMenu($value['id'])) {
				$dropdownString=prepareDropdownString($value);
				#$dropdownString=str_replace('{{username}}',$GLOBALS['user']->data()->username,$dropdownString); # There *is* no $GLOBALS['user']->...->username because we're not logged in
				echo $dropdownString;
			}
		}
	}
}
?>
	</ul>
  </div><!--/.nav-collapse -->
</div><!--/.container-fluid -->
</nav>
