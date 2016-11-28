<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

ini_set("allow_url_fopen", 1);

$reCaptchaValid=FALSE;
$fieldList = [
    'username' => new FormField_Text('users.username', [
        'placeholder' => lang('USERNAME_OR_EMAIL'),
        'new_valid' => ['unique'=>'unset'], // don't require unique in users
        'extra'     => 'autofocus',
    ]),
    'password' => new FormField_Password('users.password', [
        'new_valid' => [ ], // accept all defaults
        'extra'     => 'autocomplete="off"',
    ]),
    'recaptcha' => new FormField_Recaptcha('recaptcha', [
        'label' => lang('COMPLETE_RECAPTCHA'),
    ]),
    'remember' => new FormField_Checkbox('remember', [
        'label' => lang('REMEMBER_ME'),
    ]),
    '<div class="text-center">'."\n",
    'sign_in' => new FormField_ButtonSubmit('sign_in', [
        'label' => lang('SIGN_IN'),
        '{BUTTON-ICON}' => 'fa fa-sign-in',
    ]),
    'forgot_password' => new FormField_ButtonAnchor('forgot_password', [
        'label' => lang('FORGOT_PASSWD'),
        '{HREF}' => 'forgot_password.php',
        '{BUTTON-ICON}' => 'fa fa-wrench',
    ]),
    'join' => new FormField_ButtonAnchor('join', [
        'label' => lang('SIGN_UP'),
        '{HREF}' => 'join.php',
        '{BUTTON-ICON}' => 'fa fa-plus-square',
    ]),
    '</div>'."\n",
];
$myForm = new Form($fieldList, [
    'title' => 'SIGN_IN',
    'conditional_fields' => ['recaptcha'=>configGet('recaptcha'), 'remember'=>configGet('allow_remember_me')]]);

/*
If enabled, insert google and facebook auth url generators
*/
if (configGet('glogin')) {
	require_once pathFinder('helpers/glogin.php');
}
if (configGet('fblogin')) {
	require_once pathFinder('helpers/fblogin.php');
}

checkToken();

if (Input::exists()) {
	if ($myForm->checkFieldValidation($_POST, $errors)) {
		# Log user in
		$remember = (Input::get('remember') === 'on') ? true : false;
		$user = new User();
		$login = $user->loginEmail(Input::get('username'), trim(Input::get('password')), $remember);
		if ($login) {
            # If the user tried to go to a given page (other than login.php) and redirect_referrer_login
            # is turned on, then now that we are logged in go to that page.
			if ($_SESSION['securePageRequest'] && basename($_SESSION['securePageRequest']) != 'login.php' && configGet('redirect_referrer_login')) {
				//bold('HERE');
				$securePageRequest=$_SESSION['securePageRequest'];
				unset($_SESSION['securePageRequest']);
				Redirect::to($securePageRequest);
			} else {
                # Modify the post-login redirect destination by modifying redirect_login in the
                # `settings` database table
				Redirect::to(US_URL_ROOT.configGet('redirect_login'));
			}
		} else {
			$errors[]= lang('LOGIN_FAILED');
		}
	}
}

echo $myForm->getHTMLHeader();
echo $myForm->getHTMLOpenContainer();
echo $myForm->getHTMLOpenRowCol();
echo $myForm->getHTMLTitleAndResults(['errors'=>$errors, 'successes'=>$successes]);
echo $myForm->getHTMLCloseColRow();
echo $myForm->getHTMLOpenForm();
echo $myForm->getHTMLOpenRowCol();
echo $myForm->getHTMLFields();
echo $myForm->getHTMLCloseColRow();
echo $myForm->getHTMLCloseForm();
echo $myForm->getHTMLCloseContainer();
echo $myForm->getHTMLPageFooter();
echo $myForm->getHTMLFooter();

if (configGet('recaptcha') == 1) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php
}
?>
