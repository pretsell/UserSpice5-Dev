<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/header.php';

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}
checkToken();

if(!empty($_POST['settings'])){

	if(configGet('force_ssl') != $_POST['force_ssl']) {
		$force_ssl = Input::get('force_ssl');
		$fields=array('force_ssl'=>$force_ssl);
		$db->update('settings',1,$fields);
	}
	if(configGet('recaptcha') != $_POST['recaptcha']) {
		$recaptcha = Input::get('recaptcha');
		$fields=array('recaptcha'=>$recaptcha);
		$db->update('settings',1,$fields);
	}
	if(configGet('recaptcha_private') != $_POST['recaptcha_private']) {
		$recaptcha_private = Input::get('recaptcha_private');
		$fields=array('recaptcha_private'=>$recaptcha_private);
		$db->update('settings',1,$fields);
	}
	if(configGet('recaptcha_public') != $_POST['recaptcha_public']) {
		$recaptcha_public = Input::get('recaptcha_public');
		$fields=array('recaptcha_public'=>$recaptcha_public);
		$db->update('settings',1,$fields);
	}

	if(configGet('session_timeout') != $_POST['session_timeout']) {
		$session_timeout = Input::get('session_timeout');
		$fields=array('session_timeout'=>$session_timeout);
		$db->update('settings',1,$fields);
	}

	if(configGet('allow_remember_me') != $_POST['allow_remember_me']) {
		$allow_remember_me = Input::get('allow_remember_me');
		$fields=array('allow_remember_me'=>$allow_remember_me);
		$db->update('settings',1,$fields);
	}

	Redirect::to('admin_security.php');
}

?>
<div class="row"> <!-- row for Users, Permissions, Pages, Email settings panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=configGet('version')?></h1>
	<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
</div> <!-- /.row -->

<div class="row"> <!-- rows for Main Settings -->
	<div class="col-xs-12"> <!-- Site Settings Column -->
		<form class="" action="admin_security.php" name="settings" method="post">
		<h2 >Site Security</h2>

		<!-- Force SSL -->
		<div class="form-group">
			<label for="force_ssl">Force SSL (experimental)</label>
			<select id="force_ssl" class="form-control" name="force_ssl">
				<option value="1" <?php if(configGet('force_ssl')==1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if(configGet('force_ssl')==0) echo 'selected="selected"'; ?> >No</option>
			</select>
		</div>

		<!-- Recaptcha Option -->
		<div class="form-group">
			<label for="recaptcha">Recaptcha</label>
			<select id="recaptcha" class="form-control" name="recaptcha">
				<option value="1" <?php if(configGet('recaptcha')==1) echo 'selected="selected"'; ?> >Enabled</option>
				<option value="0" <?php if(configGet('recaptcha')==0) echo 'selected="selected"'; ?> >Disabled</option>
			</select>
		</div>

		<!-- recaptcha_private -->
		<div class="form-group">
			<label for="recaptcha_private">Recaptcha Private Key</label>
			<input type="text" class="form-control" name="recaptcha_private" id="recaptcha_private" value="<?=configGet('recaptcha_private')?>">
		</div>

		<!-- recaptcha_public -->
		<div class="form-group">
			<label for="recaptcha_public">Recaptcha Public Key</label>
			<input type="text" class="form-control" name="recaptcha_public" id="recaptcha_public" value="<?=configGet('recaptcha_public')?>">
		</div>

		<!-- session_timeout -->
		<div class="form-group">
			<label for="session_timeout">Session Timeout (in seconds: 3600 = 1 hour)</label>
			<input type="text" class="form-control" name="session_timeout" id="session_timeout" value="<?=configGet('session_timeout')?>">
		</div>

		<!-- allow_remember_me -->
		<div class="form-group">
			<label for="allow_remember_me">Allow Remember Me</label>
			<select id="allow_remember_me" class="form-control" name="allow_remember_me">
				<option value="1" <?php if(configGet('allow_remember_me')==1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if(configGet('allow_remember_me')==0) echo 'selected="selected"'; ?> >No</option>
			</select>
		</div>

		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<p><input class='btn btn-primary' type='submit' name="settings" value='Save Site Settings' /></p>
		</form>
	</div> <!-- /col1/2 -->
</div> <!-- /row -->

<!-- footers -->
<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
