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
$reset_password_success=FALSE;
$password_change_form=FALSE;

if(Input::exists()){
	if(!Token::check(Input::get('csrf'))){
		die('Token doesn\'t match!');
	}
}

if(Input::get('reset') == 1){ //$_GET['reset'] is set when clicking the link in the password reset email.

	//display the reset form.
	$email = Input::get('email');
	$vericode = Input::get('vericode');
	$ruser = new User($email);
	if (Input::get('resetPassword')) {

		$validate = new Validate();
		$validation = $validate->check($_POST,array(
		'password' => array(
		  'display' => 'New Password',
		  'required' => true,
		  'min' => 6,
		),
		'confirm' => array(
		  'display' => 'Confirm Password',
		  'required' => true,
		  'matches' => 'password',
		),
		));
		if($validation->passed()){
			//update password
			$ruser->update(array(
			  'password' => password_hash(Input::get('password'), PASSWORD_BCRYPT, array('cost' => 12)),
			  'vericode' => rand(100000,999999),
				'email_verified' => true,
			),$ruser->data()->id);
			$reset_password_success=TRUE;
		}else{
			$reset_password_success=FALSE;
			/*
			Validation did not pass so copy errors from validation class to $errors[]
			*/
			foreach ($validation->errors() as $error) {
				$errors[] = $error;
			}
		}
	}
	if ($ruser->exists() && $ruser->data()->vericode == $vericode) {
		//if the user email is in DB and verification code is correct, show the form
		$password_change_form=TRUE;
	}
}

?>

<?php
if ((Input::get('reset') == 1)){
	if($reset_password_success){
?>
		<div class="row">
		<div class="col-xs-12">
		<div class="jumbotron text-center">
		<h2>Your password has been reset!</h2>
		<p><a href="login.php" class="btn btn-primary">Login</a></p>
		</div>	
		</div><!-- /.col -->
		</div><!-- /.row -->
		
<?php
		}elseif((!Input::get('resetPassword') || !$reset_password_success) && $password_change_form){
?>

		<div class="row">
		<div class="col-xs-12">

		<div class="jumbotron container">
			<h2 class="text-center">Hello <?=$ruser->data()->fname;?>,</h2>
			<p class="text-center">Please reset your password.</p>
			<form action="password_reset.php?reset=1" method="post">
				<?=display_errors($errors);?>
				<div class="form-group">
					<label for="password">New Password:</label>
					<input type="password" name="password" value="" id="password" class="form-control">
				</div>
				<div class="form-group">
					<label for="confirm">Confirm Password:</label>
					<input type="password" name="confirm" value="" id="confirm" class="form-control">
				</div>
				<input type="hidden" name="csrf" value="<?=Token::generate();?>">
				<input type="hidden" name="email" value="<?=$email;?>">
				<input type="hidden" name="vericode" value="<?=$vericode;?>">
				<input type="submit" name="resetPassword" value="Reset" class="btn btn-primary">
			</form>
		</div>	
		</div><!-- /.col -->
		</div><!-- /.row -->
<?php
	}else{
?>
		<div class="row">
		<div class="col-xs-12">
		<div class="jumbotron text-center">
		<h2>Oops...something went wrong, maybe an old reset link you clicked on. Click below to try again</h2>
		<p><a href="forgot_password.php" class="btn btn-primary">Reset Password</a></p>
		</div>	
		</div><!-- /.col -->
		</div><!-- /.row -->
<?php
	}
}else{
?>
		<div class="row">
		<div class="col-xs-12">
		<div class="jumbotron text-center">
		<h2>Oops...something went wrong, maybe an old reset link you clicked on. Click below to try again</h2>
		<p><a href="forgot_password.php" class="btn btn-primary">Reset Password</a></p>
		</div>	
		</div><!-- /.col -->
		</div><!-- /.row -->
<?php
}
?>


