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

if($user->data()->id != 1){
  Redirect::to('account.php');
}

if(!empty($_POST)){
	$email = Input::get('email');
	$subject = 'Testing Your Email Settings!';
	$body = 'This is the body of your test email';
	/*
	Below mail function turns on high (level 3) verbosity for the PHPMailer connection
	*/
	$mail_result=email($email,$subject,$body,false,3);
}

?>
<div class="row"> <!-- row for Users, Permissions, Pages, Email settings panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=configGet('version')?></h1>
	<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
</div> <!-- /.row -->
<div class="row">
<div class="col-xs-12">
<h1>Test your email settings.</h1><br>
It's a good idea to test to make sure you can actually receive system emails before forcing your users to verify theirs. <br><br>
<?php
if (Input::exists()){
	if($mail_result[0]){
		if(configGet('mail_method')=='smtp'){
			echo '<h3>PHPMailer Transaction Log</h3>';
			echo '<pre>'.$mail_result[1].'</pre>';
		}
		echo '<div class="alert alert-success">Mail sent successfully</div>';

	}else{
		if(configGet('mail_method')=='smtp'){
			echo '<h3>PHPMailer Transaction Log</h3>';
			echo '<pre>'.$mail_result[1].'</pre>';
		}
		echo '<div class="alert alert-danger" role="alert">Mail ERROR. See above transaction log for details.</div>';
	}
}
?>
<form name="email" action="admin_email_test.php" method="post">
	<div class="form-group">
		<label>Send test to (Ideally different than your from address):</label>
		<input required size='50' class='form-control' type='text' name='email' value='' />
	</div>

	<input class='btn btn-primary' type='submit' value='Send A Test Email' class='submit' />
</form>
</div>
</div>
    <!-- footers -->
<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->

<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
