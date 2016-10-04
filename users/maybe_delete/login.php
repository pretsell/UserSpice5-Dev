<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

ini_set("allow_url_fopen", 1);
require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';

$errors=array();
$successes=array();
$reCaptchaValid=FALSE;

/*
If $_POST data exists, then check CSRF token, and kill page if not correct...no need to process rest of page or form data
*/
if (Input::exists()) {
	if(!Token::check(Input::get('csrf'))){
		die('Token doesn\'t match!');
	}
}


if (Input::exists()) {
	/*
	If recaptcha is enabled, then process recaptcha and response
	*/
	if($site_settings->recaptcha == 1){
		$remoteIp=$_SERVER["REMOTE_ADDR"];
		$gRecaptchaResponse=Input::get('g-recaptcha-response');
		$response = null;
		
		require_once 'includes/recaptcha.config.php';

		// check secret key
		$reCaptcha = new ReCaptcha($site_settings->recaptcha_private);

		// if submitted check response
		if ($gRecaptchaResponse) {
			$response = $reCaptcha->verifyResponse($remoteIp,$gRecaptchaResponse);
		}
		if ($response != null && $response->success) {
			$reCaptchaValid=TRUE;
		}else{
			$reCaptchaValid=FALSE;
			$errors[]='Please check the reCaptcha';
		}
	}else{
		/*
		If reCaptcha is disabled, then set true so that the following login sequence will run
		*/
		$reCaptchaValid=TRUE;
	}

	if($reCaptchaValid || $site_settings->recaptcha == 0){ //if recaptcha valid or recaptcha disabled

		$validate = new Validate();
		$validation = $validate->check($_POST, array('username' => array('display' => 'Username','required' => true),'password' => array('display' => 'Password', 'required' => true)));

		if ($validation->passed()) {
			//Log user in

			$remember = (Input::get('remember') === 'on') ? true : false;
			$user = new User();
			$login = $user->loginEmail(Input::get('username'), trim(Input::get('password')), $remember);
			if ($login) {
				/*
				Check if custom login script is present, and execute if necessary
				*/
				if(file_exists(ABS_US_ROOT.US_URL_ROOT.'usersc/scripts/custom_login_script.php')){
					require_once ABS_US_ROOT.US_URL_ROOT.'usersc/scripts/custom_login_script.php';
				}else{
					/*
					Feel free to change where the user goes after login!
					*/
					Redirect::to('profile.php');
				}
			} else {
				$errors[]= 'Log in failed. Please check your username and password and try again';
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
}

?>

<div class="row">
	<div class="col-xs-12">
	<?=display_errors($errors)?>
	<?=display_successes($successes)?>
	
	<form name="login" class="form-signin" action="login.php" method="post">
	<h2 class="form-signin-heading"> Sign In</h2>

	<div class="form-group">
		<label for="username" >Username OR Email</label>
		<input  class="form-control" type="text" name="username" id="username" placeholder="Username/Email" required autofocus>
	</div>

	<div class="form-group">
		<label for="password">Password</label>
		<input type="password" class="form-control"  name="password" id="password"  placeholder="Password" required autocomplete="off">
	</div>

	<?php
	if($site_settings->recaptcha == 1){
	?>
	<div class="form-group">
		<label>Please check the box below to continue</label>
		<div class="g-recaptcha" data-sitekey="<?=$site_settings->recaptcha_public; ?>"></div>
	</div>
	<?php } ?>

	<div class="form-group">
		<label for="remember">Remember Me</label><br/>
		<input type="checkbox" name="remember" id="remember" >
	</div>

	<input type="hidden" name="csrf" value="<?=Token::generate(); ?>">
	
	<div class="text-center">
		<button class="btn btn-primary" type="submit"><span class="fa fa-sign-in"></span> Sign In</button>
		<a href="forgot_password.php" class="btn btn-primary" type="button"><span class="fa fa-wrench"></span> Forgot Password</a>
		<a href="join.php" class="btn btn-primary" type="button"><span class="fa fa-plus-square"></span> Register</a>
	</div>
	</form>
	</div>
</div>

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; ?>

<!-- Place any per-page javascript here -->

<?php 	if($site_settings->recaptcha == 1){ ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php }
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; ?>
