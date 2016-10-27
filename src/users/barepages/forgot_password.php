<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}

$errors = array();
$email_sent=FALSE;

if(Input::exists()){
	if(!Token::check(Input::get('csrf'))){
		die('Token doesn\'t match!');
	}
}
$validation = new Validate(array('email' => array('unique'=>'unset')));

if (Input::get('forgotten_password')) {
	$email = Input::get('email');
	$fuser = new User($email);

	$validation->check($_POST);
	if($validation->passed()){
		if($fuser->exists()){
			//send the email
			/*
			URL includes the <a> and </a> tags, including the url string plus the link text
			*/
			$url='<a href="'.$site_settings->site_url.'/users/password_reset.php?email='.rawurlencode($email).'&vericode='.$fuser->data()->vericode.'&reset=1">Reset Password</a>';
			$options = array(
			  'fname' => $fuser->data()->fname,
			  'url' => $url,
			  'sitename' => $site_settings->site_name,
			);
			$subject = 'Password Reset';
			$body =  email_body($site_settings->forgot_password_template,$options);
			$email_sent=email($email,$subject,$body);
			if(!$email_sent){
				$errors[] = 'Email NOT sent due to error. Please contact site administrator.';
			}
		}else{
			$errors[] = 'That email does not exist in our database';
		}
	}else{
		$errors = stackErrorMessages($errors);
	}
}

if($email_sent){
?>
	<div class="row">
	<div class="col-xs-12">
	<div class="jumbotron">
	<p>Your password reset link has been sent to your email address.</p>
	<p>Click the link in the email to Reset your password. Be sure to check your spam folder if the email isn't in your inbox.</p>
	</div>
	</div><!-- /.col -->
	</div><!-- /.row -->
<?php
}else{
?>
	<div class="row">
	<div class="col-xs-12">
	<h1>Reset your password.</h1>
	<ol>
		<li>Enter your email address and click Reset</li>
		<li>Check your email and click the link that is sent to you.</li>
		<li>Follow the on screen instructions</li>
	</ol>
	<?=display_errors($errors);?>
	<form action="forgot_password.php" method="post" class="form ">
		<div class="form-group">
			<label for="email">Email</label>
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('email') ?>"></span>
			<input type="text" name="email" placeholder="Email Address" class="form-control" autofocus>
		</div>
		<input type="hidden" name="csrf" value="<?=Token::generate();?>">
		<p><input type="submit" name="forgotten_password" value="Reset" class="btn btn-primary"></p>
	</form>
	</div><!-- /.col -->
	</div><!-- /.row -->
<?php
}
?>
