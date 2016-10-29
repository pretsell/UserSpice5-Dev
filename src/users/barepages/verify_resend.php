<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

$errors = $successes = [];

if($user->isLoggedIn()){
	$user->logout();
	Redirect::to('verify_resend.php');
}

checkToken();

$email_sent=FALSE;

if(Input::exists('post')){
	$email = Input::get('email');
	$fuser = new User($email);

	$validate = new Validate();
	$validation = $validate->check($_POST,array(
	'email' => array(
	  'display' => 'Email',
	  'valid_email' => true,
	  'required' => true,
	),
	));
	if($validation->passed()){ //if email is valid, do this

		if($fuser->exists()){
			//send the email
			/*
			URL includes the <a> and </a> tags, including the url string plus the link text
			*/
			$url='<a href="'.$$cfg->get('site_url').'/users/verify.php?email='.rawurlencode($email).'&vericode='.$fuser->data()->vericode.'">Verify your email</a>';
			$options = array(
			  'fname' => $fuser->data()->fname,
			  'url' => $url,
			  'sitename' => $$cfg->get('site_name'),
			);
			$subject = 'Verify Your Email';
			$body =  email_body($$cfg->get('email_verify_template'),$options);
			$email_sent=email($email,$subject,$body);
			if(!$email_sent){
				$errors[] = 'Email NOT sent due to error. Please contact site administrator.';
			}
		}else{
			$errors[] = 'That email does not exist in our database';
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

if ($email_sent){
	?>
	<div class="row">
	<div class="col-xs-12">
	<div class="jumbotron text-center">
	<h2>Thank You! Your verification email has been sent.</h2>
	</div>
	</div>
	</div>
	<?php
}else{
?>
	<div class="row">
	<div class="col-xs-12">
	<div class="jumbotron">
		<h2>Verify Your Email</h2>
		<ol>
			<li>Enter your email address and click Resend</li>
			<li>Check your email and click the link that is sent to you</li>
			<li>Done</li>
		</ol>
		<form class="" action="verify_resend.php" method="post">
		<?=display_errors($errors);?>
		<div class="form-group">
			<label for="email">Enter Your Email</label>
			<input class="form-control" type="text" id="email" name="email" placeholder="Email">
		</div>
		<input type="hidden" name="csrf" value="<?=Token::generate();?>">
		<input type="submit" value="Resend" class="btn btn-primary">
		</form>
	</div>
	</div>
	</div>
<?php
}

?>
