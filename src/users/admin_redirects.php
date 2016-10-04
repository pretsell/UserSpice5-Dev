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

if(Input::exists()){
	if(!Token::check(Input::get('csrf'))){
		die('Token doesn\'t match!');
	}
}

if(!empty($_POST['settings'])){
	
	if($site_settings->redirect_login != $_POST['redirect_login']) {
		$redirect_login = Input::get('redirect_login');
		$fields=array('redirect_login'=>$redirect_login);
		$db->update('settings',1,$fields);
	}	
	if($site_settings->redirect_logout != $_POST['redirect_logout']) {
		$redirect_logout = Input::get('redirect_logout');
		$fields=array('redirect_logout'=>$redirect_logout);
		$db->update('settings',1,$fields);
	}	
	if($site_settings->redirect_deny_nologin != $_POST['redirect_deny_nologin']) {
		$redirect_deny_nologin = Input::get('redirect_deny_nologin');
		$fields=array('redirect_deny_nologin'=>$redirect_deny_nologin);
		$db->update('settings',1,$fields);
	}		
	
	if($site_settings->redirect_deny_noperm != $_POST['redirect_deny_noperm']) {
		$redirect_deny_noperm = Input::get('redirect_deny_noperm');
		$fields=array('redirect_deny_noperm'=>$redirect_deny_noperm);
		$db->update('settings',1,$fields);
	}
	
	if($site_settings->redirect_referrer_login != $_POST['redirect_referrer_login']) {
		$redirect_referrer_login = Input::get('redirect_referrer_login');
		$fields=array('redirect_referrer_login'=>$redirect_referrer_login);
		$db->update('settings',1,$fields);
	}		
	
	Redirect::to('admin_redirects.php');
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
		<form class="" action="admin_redirects.php" name="settings" method="post">
		<h2 >Action Redirects</h2>

		<!-- redirect_login -->
		<div class="form-group">
			<label for="redirect_login">Redirect on login</label>
			<input type="text" class="form-control" name="redirect_login" id="redirect_login" value="<?=$site_settings->redirect_login?>">
		</div>		

		<!-- redirect_logout -->
		<div class="form-group">
			<label for="redirect_logout">Redirect on logout</label>
			<input type="text" class="form-control" name="redirect_logout" id="redirect_logout" value="<?=$site_settings->redirect_logout?>">
		</div>
		
		<!-- redirect_deny_nologin -->
		<div class="form-group">
			<label for="redirect_deny_nologin">Redirect on page deny when not logged in</label>
			<input type="text" class="form-control" name="redirect_deny_nologin" id="redirect_deny_nologin" value="<?=$site_settings->redirect_deny_nologin?>">
		</div>
		
		<!-- redirect_deny_noperm -->
		<div class="form-group">
			<label for="redirect_deny_noperm">Redirect on page deny when no permissions</label>
			<input type="text" class="form-control" name="redirect_deny_noperm" id="redirect_deny_noperm" value="<?=$site_settings->redirect_deny_noperm?>">
		</div>
	
		<!-- redirect_referrer_login -->
		<div class="form-group">
			<label for="redirect_referrer_login">Redirect to last secured page when not logged in</label>
			<select id="redirect_referrer_login" class="form-control" name="redirect_referrer_login">
				<option value="1" <?php if($site_settings->redirect_referrer_login==1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if($site_settings->redirect_referrer_login==0) echo 'selected="selected"'; ?> >No</option>
			</select>
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
