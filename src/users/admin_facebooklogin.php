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
	if($$cfg->get('fblogin') != $_POST['fblogin']) {
		$fblogin = Input::get('fblogin');
		$fields=array('fblogin'=>$fblogin);
		$db->update('settings',1,$fields);
	}
	if($$cfg->get('fbid') != $_POST['fbid']) {
		$fbid = Input::get('fbid');
		$fields=array('fbid'=>$fbid);
		$db->update('settings',1,$fields);
	}
	if($$cfg->get('fbsecret') != $_POST['fbsecret']) {
		$fbsecret = Input::get('fbsecret');
		$fields=array('fbsecret'=>$fbsecret);
		$db->update('settings',1,$fields);
	}
	if($$cfg->get('fbcallback') != $_POST['fbcallback']) {
		$fbcallback = Input::get('fbcallback');
		$fields=array('fbcallback'=>$fbcallback);
		$db->update('settings',1,$fields);
	}
	Redirect::to('admin_facebooklogin.php');
}

?>
<div class="row"> <!-- row for Users, Permissions, Pages, Email settings panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$$cfg->get('version')?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
</div> <!-- /.row -->

<div class="row"> <!-- rows for Main Settings -->
	<div class="col-xs-12"> <!-- Site Settings Column -->
		<form class="" action="admin_facebooklogin.php" name="settings" method="post">
		<h2 >Facebook Login Settings</h2>

		<!-- fblogin -->
		<div class="form-group">
			<label for="fblogin">Feature State</label>
			<select id="fblogin" class="form-control" name="fblogin">
				<option value="1" <?php if($$cfg->get('fblogin')==1) echo 'selected="selected"'; ?> >Enabled</option>
				<option value="0" <?php if($$cfg->get('fblogin')==0) echo 'selected="selected"'; ?> >Disabled</option>
			</select>
		</div>

		<!-- fbid -->
		<div class="form-group">
			<label for="fbid">Client/App ID</label>
			<input type="text" class="form-control" name="fbid" id="fbid" value="<?=$$cfg->get('fbid')?>">
		</div>

		<!-- fbsecret -->
		<div class="form-group">
			<label for="fbsecret">Secret</label>
			<input type="text" class="form-control" name="fbsecret" id="fbsecret" value="<?=$$cfg->get('fbsecret')?>">
		</div>

		<!-- fbcallback -->
		<div class="form-group">
			<label for="fbcallback">Callback</label>
			<input type="text" class="form-control" name="fbcallback" id="fbcallback" value="<?=$$cfg->get('fbcallback')?>">
		</div>

		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<p><input class='btn btn-primary' type='submit' name="settings" value='Save Google Settings' /></p>
		</form>
	</div> <!-- /col1/2 -->
</div> <!-- /row -->

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
