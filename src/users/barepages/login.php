<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

ini_set("allow_url_fopen", 1);

$errors=array();
$successes=array();
$reCaptchaValid=FALSE;
$validation = new Validate([
	'username' => ['unique'=>'unset'],
	'password'
]);

/*
If enabled, insert google and facebook auth url generators
*/
if ($site_settings->glogin) {
	require_once ABS_US_ROOT.US_URL_ROOT.'users/helpers/glogin.php';
}
if ($site_settings->fblogin) {
	require_once ABS_US_ROOT.US_URL_ROOT.'users/helpers/fblogin.php';
}


/*
If $_POST data exists, then check CSRF token, and kill page if not correct...no need to process rest of page or form data
*/
if (Input::exists()) {
	if (!Token::check(Input::get('csrf'))) {
		die('Token doesn\'t match!');
	}
}


if (Input::exists()) {
	/*
	If recaptcha is enabled, then process recaptcha and response
	*/
	if ($site_settings->recaptcha == 1) {
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
		} else {
			$reCaptchaValid=FALSE;
			$errors[]='Please check the reCaptcha';
		}
	} else {
		/*
		If reCaptcha is disabled, then set true so that the following login sequence will run
		*/
		$reCaptchaValid=TRUE;
	}

	if ($reCaptchaValid || $site_settings->recaptcha == 0) { //if recaptcha valid or recaptcha disabled

		$validation->check($_POST);

		if ($validation->passed()) {
			//Log user in

			$remember = (Input::get('remember') === 'on') ? true : false;
			$user = new User();
			$login = $user->loginEmail(Input::get('username'), trim(Input::get('password')), $remember);
			if ($login) {
				/*
				Feel free to change where the user goes after login!
				*/
				if ($_SESSION['securePageRequest'] && $site_settings->redirect_referrer_login) {
					//bold('HERE');
					$securePageRequest=$_SESSION['securePageRequest'];
					unset($_SESSION['securePageRequest']);
					Redirect::to($securePageRequest);
				} else {
					Redirect::to(US_URL_ROOT.$site_settings->redirect_login);
				}
			} else {
				$errors[]= 'Log in failed. Please check your username and password and try again';
			}
		} else {
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

	<form name="login" class="form" action="login.php" method="post">
	<h2 class="text-center"> Sign In</h2>

	<div class="form-group">
		<label class="control-label" for="username">Username OR Email</label>
		<span class="glyphicon glyphicon-question-sign" title="<?= $validation->describe('username') ?>"></span>
		<div><input  class="form-control" type="text" name="username" id="username" placeholder="Username/Email" required autofocus></div>
	</div>

	<div class="form-group">
		<label class="control-label" for="password">Password</label>
		<span class="glyphicon glyphicon-question-sign" title="<?= $validation->describe('password') ?>"></span>
		<div><input type="password" class="form-control"  name="password" id="password"  placeholder="Password" required autocomplete="off"></div>
	</div>

	<?php
	if ($site_settings->recaptcha == 1) {
	?>
	<div class="form-group">
		<label>Please check the box below to continue</label>
		<div class="g-recaptcha" data-sitekey="<?=$site_settings->recaptcha_public; ?>"></div>
	</div>
	<?php } ?>

	<?php
	if ($site_settings->allow_remember_me == 1) {
	?>
	<div class="form-group">
		<label class="control-label" for="remember">Remember Me</label>
		<div><input type="checkbox" name="remember" id="remember" ></div>
	</div>
	<?php
	}
	?>
	<input type="hidden" name="csrf" value="<?=Token::generate(); ?>">

	<div class="text-center">
		<button class="btn btn-primary" type="submit"><span class="fa fa-sign-in"></span> Sign In</button>
		<?php	if ($site_settings->glogin) {?><a href="<?=$gAuthUrl?>" type="button"><img src="<?=US_URL_ROOT.'users/images/google.png'?>" height="35px"></a><?php } ?>
		<?php if ($site_settings->fblogin) {?><a href="<?=$fbAuthUrl?>" type="button"><img src="<?=US_URL_ROOT.'users/images/facebook.png'?>" height="35px"></a><?php } ?>
		<a href="forgot_password.php" class="btn btn-primary" type="button"><span class="fa fa-wrench"></span> Forgot Password</a>
		<a href="join.php" class="btn btn-primary" type="button"><span class="fa fa-plus-square"></span> Register</a>
	</div>
	</form>
	</div>
</div>

<!-- Place any per-page javascript here -->
<?php 	if ($site_settings->recaptcha == 1) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php } ?>
