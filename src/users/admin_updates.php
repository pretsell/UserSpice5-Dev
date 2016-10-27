<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}
checkToken();

if(!empty($_POST['submit'])){
	Redirect::to('admin_updates.php');
}

if(Input::exists('get')){

}else{

}

?>
<div class="row"> <!-- row for Users, Permissions, Pages, Email settings panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
</div> <!-- /.row -->

<div class="row"> <!-- rows for Main Settings -->
	<div class="col-xs-12"> <!-- Site Settings Column -->
		<h2>Updates</h2>

		<form class="" action="admin_updates.php" name="settings" method="post">


		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<input class='btn btn-primary' type='submit' name="submit" value='Save Site Settings' />

		<a class="btn btn-primary" href="admin_updates.php?action=checkversion">Check For Latest Version</a>

		</form>
	</div> <!-- /col1/2 -->
</div> <!-- /row -->

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
