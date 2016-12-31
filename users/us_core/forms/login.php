<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

ini_set("allow_url_fopen", 1);

$reCaptchaValid=FALSE;
        $MACRO_Value = '';
$fieldList = [
    'username' => new FormField_Text([
        'dbfield' => 'users.username',
        'placeholder' => lang('USERNAME_OR_EMAIL'),
        'new_valid' => ['unique'=>'unset'], // don't require unique in users
        'extra'     => 'autofocus',
    ]),
    'password' => new FormField_Password([
        'dbfield' => 'users.password',
        'new_valid' => [ ], // accept all defaults
        'extra'     => 'autocomplete="off"',
    ]),
    'recaptcha' => new FormField_Recaptcha([
        'dbfield' => 'recaptcha',
        'display' => lang('COMPLETE_RECAPTCHA'),
        'keep_if' => configGet('recaptcha'),
    ]),
    'remember' => new FormField_Checkbox([
        'dbfield' => 'remember',
        'display' => lang('REMEMBER_ME'),
        'keep_if' => configGet('allow_remember_me'),
    ]),
    '<div class="text-center">'."\n",
    'sign_in' => new FormField_ButtonSubmit([
        'dbfield' => 'sign_in',
        'display' => lang('SIGN_IN'),
        'Button_Icon' => 'fa fa-sign-in',
    ]),
    'forgot_password' => new FormField_ButtonAnchor([
        'dbfield' => 'forgot_password',
        'display' => lang('FORGOT_PASSWD'),
        'Link' => 'forgot_password.php',
        'Button_Icon' => 'fa fa-wrench',
    ]),
    'join' => new FormField_ButtonAnchor([
        'dbfield' => 'join',
        'display' => lang('SIGN_UP'),
        'Link' => 'join.php',
        'Button_Icon' => 'fa fa-plus-square',
    ]),
    '</div>'."\n",
];
$myForm = new Form($fieldList, [
    'title' => lang('SIGN_IN'),
    'elements' => ['Header', 'openContainer', 'openRow', 'openCol',
                    'TitleAndResults',
                    'openForm', 'Fields', 'closeForm',
                    'closeCol', 'closeRow', 'closeContainer',
                    'PageFooter', 'Footer'],
    ]);

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
			if (@$_SESSION['securePageRequest'] && basename($_SESSION['securePageRequest']) != 'login.php' && configGet('redirect_referrer_login')) {
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

echo $myForm->getHTML(['errors'=>$errors, 'successes'=>$successes]);

if (configGet('recaptcha') == 1) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php
}
?>
