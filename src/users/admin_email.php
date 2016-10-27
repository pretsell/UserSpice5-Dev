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

if(isset($_POST['settings'])){
	if($site_settings->mail_method != $_POST['mail_method']) {
		$mail_method = Input::get('mail_method');
		dump($mail_method);
		$fields=array('mail_method'=>$mail_method);
		$db->update('settings',1,$fields);
	}
	if($site_settings->smtp_server != $_POST['smtp_server']) {
		$smtp_server = Input::get('smtp_server');
		$fields=array('smtp_server'=>$smtp_server);
		$db->update('settings',1,$fields);
	}
	if($site_settings->smtp_port != $_POST['smtp_port']) {
		$smtp_port = Input::get('smtp_port');
		$fields=array('smtp_port'=>$smtp_port);
		$db->update('settings',1,$fields);
	}
	if($site_settings->smtp_transport != $_POST['smtp_transport']) {
		$smtp_transport = Input::get('smtp_transport');
		$fields=array('smtp_transport'=>$smtp_transport);
		$db->update('settings',1,$fields);
	}
	if($site_settings->email_login != $_POST['email_login']) {
		$email_login = Input::get('email_login');
		$fields=array('email_login'=>$email_login);
		$db->update('settings',1,$fields);
	}
	if($site_settings->email_pass != $_POST['email_pass']) {
		$email_pass = Input::get('email_pass');
		$fields=array('email_pass'=>$email_pass);
		$db->update('settings',1,$fields);
	}
	if($site_settings->from_name != $_POST['from_name']) {
		$from_name = Input::get('from_name');
		$fields=array('from_name'=>$from_name);
		$db->update('settings',1,$fields);
	}

	if($site_settings->from_email != $_POST['from_email']) {
		$from_email = Input::get('from_email');
		$fields=array('from_email'=>$from_email);
		$db->update('settings',1,$fields);
	}

	Redirect::to('admin_email.php');
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
		<form class="" action="admin_email.php" name="settings" method="post">
		<h2 >Site Settings</h2>

		<!-- mail_method -->
		<div class="form-group">
			<label for="mail_method">Mail Method</label>
			<select class="form-control" name="mail_method">
				<option value="smtp" <?php if($site_settings->mail_method=='smtp') echo 'selected="selected"'; ?> >SMTP Server</option>
				<option value="sendmail" <?php if($site_settings->mail_method=='sendmail') echo 'selected="selected"'; ?> >Sendmail</option>
				<option value="phpmail" <?php if($site_settings->mail_method=='phpmail') echo 'selected="selected"'; ?> >PHP Mail()</option>
			</select>
		</div>

		<!-- smtp_server -->
		<div class="form-group">
			<label for="smtp_server">SMTP Server (only applies to SMTP method)</label>
			<input type="text" class="form-control" name="smtp_server" id="smtp_server" value="<?=$site_settings->smtp_server?>">
		</div>

		<!-- smtp_port -->
		<div class="form-group">
			<label for="smtp_port">SMTP Port (only applies to SMTP method)</label>
			<input type="text" class="form-control" name="smtp_port" id="smtp_port" value="<?=$site_settings->smtp_port?>">
		</div>

		<!-- smtp_transport -->
		<div class="form-group">
			<label for="smtp_transport">SMTP Transport (only applies to SMTP method)<?php echo (extension_loaded('openssl')?' OpenSSL is available on this server':' OpenSSL is NOT available on this server');?></label>
			<select class="form-control" name="smtp_transport">
				<option value="tls" <?php if($site_settings->smtp_transport=='tls') echo 'selected="selected"'; ?> >TLS (encrypted)</option>
				<option value="ssl" <?php if($site_settings->smtp_transport=='ssl') echo 'selected="selected"'; ?> >SSL (encrypted, but weak)</option>
			</select>
		</div>

		<!-- email_login -->
		<div class="form-group">
			<label for="email_login">Email Login (only applies to SMTP method)</label>
					<input type="text" class="form-control" name="email_login" id="email_login" value="<?=$site_settings->email_login?>">
		</div>

		<!-- email_pass -->
		<div class="form-group">
			<label for="email_pass">Email Password (only applies to SMTP method)</label>
			<input type="text" class="form-control" name="email_pass" id="email_pass" value="<?=$site_settings->email_pass?>">
		</div>

		<!-- from_name -->
		<div class="form-group">
			<label for="from_name">From Name</label>
			<input type="text" class="form-control" name="from_name" id="from_name" value="<?=$site_settings->from_name?>">
		</div>

		<!-- from_email -->
		<div class="form-group">
			<label for="from_email">From Email</label>
			<input type="text" class="form-control" name="from_email" id="from_email" value="<?=$site_settings->from_email?>">
		</div>

		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<input class='btn btn-primary' type='submit' name="settings" value='Save Site Settings' />
		<a class='btn btn-primary' href="admin_email_test.php">Test Email Settings</a>
		</form>
	</div> <!-- /col1/2 -->
</div> <!-- /row -->

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
