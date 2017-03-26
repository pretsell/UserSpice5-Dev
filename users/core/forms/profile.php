<?php

checkToken();

require_once(US_ROOT_DIR.'resources/vendor/autoload.php');
use ZxcvbnPhp\Zxcvbn;

if ($user->isLoggedIn()) {
	$userId = $user->data()->id;
} else {
	$userId = 0;
}

$id = Input::get('id');
if ($id==$user->data()->id || empty($id)) {
	$displayFullProfile=TRUE;
	$userData = $user->data();
    $id = $userData->id;
} else {
    // not your profile - allow editing only if you are admin
	$displayFullProfile=$user->isAdmin();
	$userData = $db->queryAll("users", ['id' => $id])->first();
}
$grav = get_gravatar(strtolower(trim($userData->email)));
$joinDate = strtotime($userData->join_date);
$joinDateStr = date(configGet('date_fmt'), $joinDate);
$zones = DateTimeZone::listIdentifiers();
$timezones = [];
foreach ($zones as $tz) {
    #$x = new DateTimeZone($tz);
    $time = new DateTime("now", new DateTimeZone($tz));
    $offsets[] = $time->getOffset();
    $offset_hours = floor($time->getOffset() / 3600);
    $offset_mins = floor(($time->getOffset() - ($offset_hours*3600)) / 60);
    $offset_display = 'GMT' . ($offset_hours < 0 ? $offset_hours : '+'.$offset_hours);
    $offset_display .= ':'.($offset_mins >= 10 ? $offset_mins : '0'.$offset_mins);
    $timezones[] = [ 'id' => $tz, 'name' => '('.$offset_display.') '. str_replace('_', ' ', $tz) . ' (currently ' . $time->format(configGet('time_fmt')) . ')' ];
    array_multisort($offsets, $timezones);
}

$myForm = new Form([
    'toc' => new FormField_TabToC,
    new FormTab_Contents([
        'tab1' => new FormTab_Pane([
            new Form_Col([
                'gravatar' => '<p><img src="'.$grav.'" class="img-thumbnail" alt="Gravatar placeholder thumbnail"></p>',
                '<div class="form-group">'.lang('CHANGE_GRAVATAR', $userData->email).'</div>',
                'joinDateStr' => new FormField_Text([
                    'isDbField' => false,
                    'display' => lang('MEMBER_SINCE'),
                    'disabled' => true,
                    'value' => $joinDateStr,
                ]),
                'logins' => new FormField_Text([
                    'disabled' => true,
                    'display' => lang('NUMBER_LOGINS'),
                    'value' => $userData->logins,
                ]),
                'id' => new FormField_Text([
                    'disabled' => true,
                    'display' => lang('USER_ID'),
                    #'value' => $userData->id,
                ])
            ], [
                'Col_Class' => 'col-xs-12 col-md-3',
            ]),
            new Form_Col([
                'users.username' => new FormField_Text([
                    #'dbname' => 'users.username',
                    'disabled' => !configGet('allow_username_change') || !$displayFullProfile,
                ]),
                'users.fname' => new FormField_Text([
                    'disabled' => !$displayFullProfile,
                ]),
                'users.lname' => new FormField_Text([
                    'disabled' => !$displayFullProfile,
                ]),
                'users.email' => new FormField_Text([
                    'deleteif' => !$displayFullProfile,
                ]),
                'timezone_string' => new FormField_Select([
                    'display' => lang('TIMEZONE'),
                    'data' => $timezones,
                    'deleteif' => !$displayFullProfile,
                ]),
                'bio' => new FormField_Textarea([
                    'disabled' => !$displayFullProfile,
                ]),
                'save' => new FormField_ButtonSubmit([
                    'deleteif' => !$displayFullProfile,
                ]),
            ], [
                'Col_Class' => 'col-xs-12 col-md-9',
            ]),
        ], [
            'title' => lang('PROFILE_TAB_TITLE'),
            'activetab' => !Input::get('change_pw'),
        ]),
        'tab2' => new FormTab_Pane([
            'curpass' => new FormField_Password([
                'display' => lang('CURRENT_PASSWORD'),
                'isDbField' => false,
            ]),
            'newpass' => new FormField_Password([
                'display' => lang('NEW_PASSWORD'),
                'isDbField' => false,
                'strengthmeter' => true,
            ]),
            'confirmpass' => new FormField_Password([
                'display' => lang('CONFIRM_NEW_PASSWORD'),
                'isDbField' => false,
                #'valid' => [ 'match' => 'newpass' ],
            ]),
            'change_pw' => new FormField_ButtonSubmit([
                'display' => lang('CHANGE_PASSWORD'),
            ])
        ], [
            'title' => lang('CHANGE_PW_TAB_TITLE'),
            'activetab' => Input::get('change_pw'),
            'deleteif' => !$displayFullProfile,
        ]),
    ]),
], [
    'dbtable' => 'users',
    'dbtableid' => $id,
    'default' => 'processing',
    'autoshow' => false, // can't display until after pw processing
]);

if (Input::get('change_pw')) {
    # Validation is done manually because the `Form` class doesn't
    # work well with cross-field validations (like making sure new
    # pw matches confirm pw)
    $validation = new Validate;
	$validation->check($_POST,[
		'curpass' => [
	      'display' => lang('CURRENT_PASSWORD'),
		  'required' => !$user->isAdmin(),
		],
		'newpass' => [
			  'display' => lang('NEW_PASSWORD'),
			  'required' => true,
		],
		'confirmpass' => [
			  'display' => lang('CONFIRM_NEW_PASSWORD'),
			  'required' => true,
			  'matches' => 'newpass',
		],
	]);
    $zxcvbn = new Zxcvbn();
	if (!$user->isAdmin() && !$user->comparePassword(Input::get('curpass'),$user->data()->password)) {
		$errors[]=lang('ACCOUNT_PASSWORD_INVALID');
	}
    if ($zxcvbn->passwordStrength('password') < configGet('min_pw_score')) {
        $errors[] = lang('PASSWORD_TOO_SIMPLE');
    }
	if ($validation->passed() && !$errors) {
		$new_pw = $user->encryptPassword(Input::get('newpass'));
		$user->update(['password'=>$new_pw],$user->data()->id);
		$successes[]='Password updated.';
	} else {
		$errors = $validation->stackErrorMessages($errors);
	}
}

echo $myForm->getHTML();
