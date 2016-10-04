<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';

clearstatcache();
/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}

if(Input::exists()){
	if(!Token::check(Input::get('csrf'))){
		die('Token doesn\'t match!');
	}
}

?>
<div class="row "> <!-- rows for Info Panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
	<div class="col-xs-12">
	<h2>PHP Info</h2>
	</div>
	<div class="col-xs-12">
	<?=phpinfo();?>
	</div> <!-- /col -->
	



</div> <!-- /row -->

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
