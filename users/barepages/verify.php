<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

/*
Will this work if the email and vericode are not specified or otherwise captured in the redirect?
*/
if($user->isLoggedIn()){
	$user->logout();
	Redirect::to(US_URL_ROOT.'users/verify_resend.php');
}

$verify_success=FALSE;
$errors = array();

if(Input::exists('get')){

	$email = Input::get('email');
	$vericode = Input::get('vericode');

	$validate = new Validate();
	$validation = $validate->check($_GET,array(
	'email' => array(
	  'display' => 'Email',
	  'valid_email' => true,
	  'required' => true,
	),
	));
	if($validation->passed()){ //if email is valid, do this
		//get the user info based on the email
		$verify = new User(Input::get('email'));

		if ($verify->exists() && $verify->data()->vericode == $vericode){ //check if this email account exists in the DB
			$verify->update(array('email_verified' => 1),$verify->data()->id);
			$verify_success=TRUE;
		}
	}else{
		/*
		Append validation errors to error array
		*/
		foreach ($validation->errors() as $error) {
			$errors[]=$error;
		}
	}
}
?>

<?php
if ($verify_success){
?>
	<div class="row">
	<div class="col-xs-12">
	<div class="jumbotron text-center">
	<h2>Your Email has been verified!</h2>
	<a href="login.php" class="btn btn-primary">Log In</a>
	</div>
	</div>
	</div>
<?php
}else{
?>
	<div class="row">
	<div class="col-xs-12">
	<div class="jumbotron text-center">
	<h1>Ooops! There was an error verifying your email address. Please click below to try again.</h1>
	<a href="verify_resend.php" class="btn btn-primary">Resend Verification Email</a>
	</div>
	</div>
	</div>
<?php
}
?>

