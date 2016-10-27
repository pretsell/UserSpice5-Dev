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
	if($$cfg->get('glogin') != $_POST['glogin']) {
		$glogin = Input::get('glogin');
		$fields=array('glogin'=>$glogin);
		$db->update('settings',1,$fields);
	}
	if($$cfg->get('gid') != $_POST['gid']) {
		$gid = Input::get('gid');
		$fields=array('gid'=>$gid);
		$db->update('settings',1,$fields);
	}
	if($$cfg->get('gsecret') != $_POST['gsecret']) {
		$gsecret = Input::get('gsecret');
		$fields=array('gsecret'=>$gsecret);
		$db->update('settings',1,$fields);
	}
	if($$cfg->get('gcallback') != $_POST['gcallback']) {
		$gcallback = Input::get('gcallback');
		$fields=array('gcallback'=>$gcallback);
		$db->update('settings',1,$fields);
	}
	Redirect::to('admin_googlelogin.php');
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
		<form class="" action="admin_googlelogin.php" name="settings" method="post">
		<h2 >Google Login Settings</h2>

		<!-- glogin -->
		<div class="form-group">
			<label for="glogin">Feature State</label>
			<select id="glogin" class="form-control" name="glogin">
				<option value="1" <?php if($$cfg->get('glogin')==1) echo 'selected="selected"'; ?> >Enabled</option>
				<option value="0" <?php if($$cfg->get('glogin')==0) echo 'selected="selected"'; ?> >Disabled</option>
			</select>
		</div>

		<!-- gid -->
		<div class="form-group">
			<label for="gid">Client/App ID</label>
			<input type="text" class="form-control" name="gid" id="gid" value="<?=$$cfg->get('gid')?>">
		</div>

		<!-- gsecret -->
		<div class="form-group">
			<label for="gsecret">Secret</label>
			<input type="text" class="form-control" name="gsecret" id="gsecret" value="<?=$$cfg->get('gsecret')?>">
		</div>

		<!-- gredirect -->
		<div class="form-group">
			<label for="gcallback">Callback</label>
			<input type="text" class="form-control" name="gcallback" id="gcallback" value="<?=$$cfg->get('gcallback')?>">
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
