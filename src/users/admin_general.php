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
	if($cfg->get('recaptcha') != $_POST['recaptcha']) {
		$recaptcha = Input::get('recaptcha');
		$fields=array('recaptcha'=>$recaptcha);
		$db->update('settings',1,$fields);
	}
	if($cfg->get('site_name') != $_POST['site_name']) {
		$site_name = Input::get('site_name');
		$fields=array('site_name'=>$site_name);
		$db->update('settings',1,$fields);
	}
	if($cfg->get('site_url') != $_POST['site_url']) {
		$site_url = Input::get('site_url');
		$fields=array('site_url'=>$site_url);
		$db->update('settings',1,$fields);
	}

	if($cfg->get('install_location') != $_POST['install_location']) {
		$install_location = Input::get('install_location');
		$fields=array('install_location'=>$install_location);
		$db->update('settings',1,$fields);
	}
	if($cfg->get('copyright_message') != $_POST['copyright_message']) {
		$copyright_message = Input::get('copyright_message');
		$fields=array('copyright_message'=>$copyright_message);
		$db->update('settings',1,$fields);
	}

	if($cfg->get('language') != $_POST['language']) {
		$language = Input::get('language');
		$fields=array('language'=>$language);
		$db->update('settings',1,$fields);
	}
	if($cfg->get('login_type') != $_POST['login_type']) {
		$login_type = Input::get('login_type');
		$fields=array('login_type'=>$login_type);
		$db->update('settings',1,$fields);
	}
	if($cfg->get('site_offline') != $_POST['site_offline']) {
		$site_offline = Input::get('site_offline');
		$fields=array('site_offline'=>$site_offline);
		$db->update('settings',1,$fields);
	}
	if($cfg->get('debug_mode') != $_POST['debug_mode']) {
		$debug_mode = Input::get('debug_mode');
		$fields=array('debug_mode'=>$debug_mode);
		$db->update('settings',1,$fields);
	}

	if($cfg->get('track_guest') != $_POST['track_guest']) {
		$track_guest = Input::get('track_guest');
		$fields=array('track_guest'=>$track_guest);
		$db->update('settings',1,$fields);
	}

	Redirect::to('admin_general.php');
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
		<form class="" action="admin_general.php" name="settings" method="post">
		<h2 >Site Settings</h2>

		<!-- Site Name -->
		<div class="form-group">
			<label for="site_name">Site Name</label>
			<input type="text" class="form-control" name="site_name" id="site_name" value="<?=$cfg->get('site_name')?>">
		</div>

		<!-- Site URL -->
		<div class="form-group">
			<label for="site_url">Site URL</label>
			<input type="text" class="form-control" name="site_url" id="site_url" value="<?=$cfg->get('site_url')?>">
		</div>

		<!-- Install Location -->
		<div class="form-group">
			<label for="install_location">Install Location</label>
			<input type="text" class="form-control" name="install_location" id="install_location" value="<?=$cfg->get('install_location')?>">
		</div>

		<!-- Copyright Message -->
		<div class="form-group">
			<label for="copyright_message">Copyright Message</label>
			<input type="text" class="form-control" name="copyright_message" id="copyright_message" value="<?=$cfg->get('copyright_message')?>">
		</div>

		<!-- Language -->
		<div class="form-group">
			<label for="language">Language</label>
			<select id="recaptcha" class="form-control" name="language">
				<option value="en" <?php if($cfg->get('language')=='en') echo 'selected="selected"'; ?> >en</option>
			</select>
		</div>

		<!-- Recaptcha Option -->
		<div class="form-group">
			<label for="recaptcha">Recaptcha</label>
			<select id="recaptcha" class="form-control" name="recaptcha">
				<option value="1" <?php if($cfg->get('recaptcha')==1) echo 'selected="selected"'; ?> >Enabled</option>
				<option value="0" <?php if($cfg->get('recaptcha')==0) echo 'selected="selected"'; ?> >Disabled</option>
			</select>
		</div>

		<!-- Site Offline -->
		<div class="form-group">
			<label for="site_offline">Site Offline</label>
			<select id="site_offline" class="form-control" name="site_offline">
				<option value="1" <?php if($cfg->get('site_offline')==1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if($cfg->get('site_offline')==0) echo 'selected="selected"'; ?> >No</option>
			</select>
		</div>

		<!-- debug_mode -->
		<div class="form-group">
			<label for="debug_mode">Debug Mode</label>
			<select id="debug_mode" class="form-control" name="debug_mode">
				<option value="1" <?php if($cfg->get('debug_mode')==1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if($cfg->get('debug_mode')==0) echo 'selected="selected"'; ?> >No</option>
			</select>
		</div>

		<!-- Track Guests -->
		<div class="form-group">
			<label for="track_guest">Track Guests</label>
			<select id="track_guest" class="form-control" name="track_guest">
				<option value="1" <?php if($cfg->get('track_guest')==1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if($cfg->get('track_guest')==0) echo 'selected="selected"'; ?> >No</option>
			</select><small>If your site gets a lot of traffic and starts to stumble, this is the first thing to turn off.</small>
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
