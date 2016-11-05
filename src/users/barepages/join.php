<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

ini_set("allow_url_fopen", 1);

#Initialize variables for the page
$errors = $successes= [];
$username = $fname = $lname = $email = '';

/*
If enabled, insert google and facebook auth url generators
*/
if (configGet('glogin')) {
	require_once ABS_US_ROOT.US_URL_ROOT.'users/helpers/glogin.php';
}
if (configGet('fblogin')) {
	require_once ABS_US_ROOT.US_URL_ROOT.'users/helpers/fblogin.php';
}

checkToken();

$reCaptchaValid=FALSE;
$createSuccess=FALSE;
$validation = new Validate([
	'username' => ['action'=>'add'],
	'fname',
	'lname',
	'email',
	'password',
	'confirm',
]);

if (Input::exists()) {

	$username = Input::get('username');
	$fname = Input::get('fname');
	$lname = Input::get('lname');
	$email = Input::get('email');
	$agreement_checkbox = Input::get('agreement_checkbox');

	/*
	If recaptcha is enabled, then process recaptcha and response
	*/
	if (configGet('recaptcha') == 1) {
		$remoteIp=$_SERVER["REMOTE_ADDR"];
		$gRecaptchaResponse=Input::get('g-recaptcha-response');
		$response = null;

		require_once 'includes/recaptcha.config.php';

		// check secret key
		$reCaptcha = new ReCaptcha(configGet('recaptcha_private'));

		// if submitted check response
		if ($gRecaptchaResponse) {
			$response = $reCaptcha->verifyResponse($remoteIp,$gRecaptchaResponse);
		}
		if ($response != null && $response->success) {
			$reCaptchaValid=TRUE;
		} else {
			$reCaptchaValid=FALSE;
			$errors[]=lang('CAPTCHA_FAIL');
		}
	} else {
		/*
		If reCaptcha is disabled, then set true so that the following sequence will run
		*/
		$reCaptchaValid=TRUE;
	}

	/*
	If agreement checkbox not checked, then add the error
	*/
	if ($agreement_checkbox=='on') {
		$agreement_checkbox=TRUE;
	} else {
		$agreement_checkbox=FALSE;
	}

	if (!$agreement_checkbox) {
		$errors[]=lang('CHECK_AGREE');
	}

	if ($reCaptchaValid || configGet('recaptcha') == 0) { //if recaptcha valid or recaptcha disabled

		/*
		Perform input validation prior to creating account
		*/
		$validation->check($_POST);
		if ($validation->passed() && $agreement_checkbox) {
			/*
			If validation passes then create user
			*/
			$vericode = rand(100000,999999);

			$user = new User();
			$join_date = date("Y-m-d H:i:s");

			if (configGet('email_act') == 1) {
				/*
				If email activation is enabled, then set email account as not verified and prepare and send email
				*/
				/*
				URL includes the <a> and </a> tags, including the url string plus the link text
				*/
				$url='<a href="'.configGet('site_url').'/users/verify.php?email='.rawurlencode($email).'&vericode='.$vericode.'">Verify your email</a>';
				$options = array('fname' => $fname,'url' => $url,'sitename' => configGet('site_name'),);

				$email_verified=0;
				$subject = 'Welcome to '.configGet('site_name').'!';
				$body = email_body(configGet('email_verify_template'),$options);
				email($email,$subject,$body);
			} else {
				/*
				Email activation is not enabled, so just flag the account as email verified
				*/
				$email_verified=1;
			}
			try {
				// echo "Trying to create user";
				$user->create(array(
					'username' => Input::get('username'),
					'fname' => Input::get('fname'),
					'lname' => Input::get('lname'),
					'email' => Input::get('email'),
					'password' =>password_hash(Input::get('password'), PASSWORD_BCRYPT, array('cost' => 12)),
					'permissions' => 1,
					'account_owner' => 1,
					'stripe_cust_id' => '',
					'join_date' => $join_date,
					'company' => '',
					'email_verified' => $email_verified,
					'active' => 1,
					'vericode' => $vericode,
				));
			} catch (Exception $e) {
				die($e->getMessage());
			}
			$createSuccess=TRUE;
		} else {
			$errors = stackErrorMessages($errors);
		}
	}
} //Input exists

?>

<div class="row">
<div class="col-xs-12">

<?php
if (!$createSuccess) {
?>
	<h2><?=lang('SIGN_UP')?></h2>
	<?=display_errors($errors);?>
	<form class="form-signup" action="join.php" method="post">

	<div class="form-group">
		<label for="username"><?=lang('CHOOSE_USERNAME')?></label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('username') ?>"></span>
		<input  class="form-control" type="text" name="username" id="username" placeholder="<?=lang('CHOOSE_USERNAME')?>" value="<?=$username;?>" required autofocus>
		<p class="help-block"><?=lang('CHOOSE_USERNAME_REQUIREMENTS')?></p>
	</div>
	<div class="form-group">
		<label for="fname"><?=lang('FNAME')?></label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('fname') ?>"></span>
		<input type="text" class="form-control" id="fname" name="fname" placeholder="<?=lang('FNAME')?>" value="<?=$fname;?>" required>
	</div>
	<div class="form-group">
		<label for="lname"><?=lang('LNAME')?></label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('lname') ?>"></span>
		<input type="text" class="form-control" id="lname" name="lname" placeholder="<?=lang('LNAME')?>" value="<?=$lname;?>" required>
	</div>
	<div class="form-group">
		<label for="email"><?=lang('EMAIL')?></label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('email') ?>"></span>
		<input  class="form-control" type="text" name="email" id="email" placeholder="<?=lang('EMAIL')?>" value="<?=$email;?>" required >
	</div>
	<div class="form-group">
		<label for="password"><?=lang('PW')?></label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('password') ?>"></span>
		<input  class="form-control" type="password" name="password" id="password" placeholder="<?=lang('PW')?>" required aria-describedby="passwordhelp">
		<span class="help-block" id="passwordhelp"><?=lang('PWR')?></span>
	</div>
	<div class="form-group">
		<label for="confirm"><?=lang('PWC')?></label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('confirm') ?>"></span>
		<input  type="password" id="confirm" name="confirm" class="form-control" placeholder="<?=lang('PWC')?>" required >
	</div>
	<div class="form-group">
		<label for="agreement"><?=lang('TNC')?></label>
		<textarea id="agreement" name="agreement" rows="5" class="form-control" disabled ><?=configGet('agreement')?></textarea>
	</div>
	<div class="form-group">
		<label for="agreement_checkbox"><?=lang('CHECK_AGREE')?></label>
		<input type="checkbox" id="agreement_checkbox" name="agreement_checkbox" >
	</div>
	<?php if (configGet('recaptcha') == 1) { ?>
	<div class="form-group">
		<div class="g-recaptcha" data-sitekey="<?=configGet('recaptcha_public'); ?>"></div>
	</div>
	<?php } ?>
	<input type="hidden" value="<?=Token::generate();?>" name="csrf">
	<div class="text-center">
	<button class="submit btn btn-primary" type="submit" id="next_button"><span class="fa fa-plus-square"></span> <?=lang('SIGN_UP')?></button>
	<?php	if (configGet('glogin')) {?><a href="<?=$gAuthUrl?>" class="" type="button"><img src="<?=US_URL_ROOT.'users/images/google.png'?>" height="35px"></a><?php } ?>
	<?php if (configGet('fblogin')) {?><a href="<?=$fbAuthUrl?>" class="" type="button"><img src="<?=US_URL_ROOT.'users/images/facebook.png'?>" height="35px"></a><?php } ?>
	</div>
	</form>
<?php
} else {
	if (configGet('email_act')==0) {
?>
		<div class="jumbotron text-center">
		<h2><?=lang('WELCOME', configGet('site_name'))?></h2>
		<p><?=lang('THANKS')?></p>
		<a href="login.php" class="btn btn-primary"><?=lang('SIGN_IN')?></a>
		</div>
<?php
	} else {
?>
		<div class="jumbotron text-center">
		<h2><?=lang('WELCOME', configGet('site_name'))?> </h2>
		<p><?=lang('THANKS_VERIFY')?></p>
		</div>
<?php
	}
}
?>

</div>
</div>

<?php 	if (configGet('recaptcha') == 1) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php } ?>
