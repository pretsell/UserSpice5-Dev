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

if(!empty($_POST['settings'])){
	if($cfg->get('site_name') != $_POST['agreement']) {
		$agreement = Input::get('agreement');
		$fields=array('agreement'=>$agreement);
		$db->update('settings',1,$fields);
	}

	if($cfg->get('email_act') != $_POST['email_act']) {
		$email_act = Input::get('email_act');
		$fields=array('email_act'=>$email_act);
		$db->update('settings',1,$fields);
	}

	Redirect::to('admin_registration.php');
}

?>
<div class="row"> <!-- row for Users, Permissions, Pages, Email settings panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$cfg->get('version')?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
</div> <!-- /.row -->

<div class="row"> <!-- rows for Main Settings -->
	<div class="col-xs-12"> <!-- Site Settings Column -->
		<form class="" action="admin_registration.php" name="settings" method="post">
		<h2>Registration</h2>

		<div class="form-group">
		  <label for="radios">Require Email Verification</label>
			<div class="">
				<label class="radio-inline" for="email_act_1">
				<input type="radio" name="email_act" id="email_act_1" value="1" <?php echo ($cfg->get('email_act')==1)?'checked':''; ?>>Yes</label>
				<label class="radio-inline" for="email_act_0">
				<input type="radio" name="email_act" id="email_act_0" value="0" <?php echo ($cfg->get('email_act')==0)?'checked':''; ?>>No</label>
			</div>
		</div>

		<div class="form-group">
			<label>Terms and Conditions</label>
			<textarea class="form-control" rows="10" id="agreement" name="agreement" ><?=$cfg->get('agreement');?></textarea></p>
		</div>

		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<p><input class='btn btn-primary' type='submit' name="settings" value='Save Site Settings' /></p>
		</form>
	</div> <!-- /col1/2 -->
</div> <!-- /row -->

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->


<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
