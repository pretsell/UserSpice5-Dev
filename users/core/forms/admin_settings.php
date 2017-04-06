<?php
/*
UserSpice
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

# Check CSRF token
checkToken();

# Calculate lists of CSS files for various selection fields
$tmp = str_replace(US_ROOT_DIR, '', array_merge(
    glob(US_ROOT_DIR.'core/css/color_schemes/*.css'),
    glob(US_ROOT_DIR.'local/css/color_schemes/*.css')
));
$css1 = [];
foreach ($tmp as $x) {
    $css1[] = ['id'=>$x, 'name'=>$x];
}
$tmp = str_replace(US_ROOT_DIR, '', array_merge(
    glob(US_ROOT_DIR.'core/css/*.css'),
    glob(US_ROOT_DIR.'local/css/*.css')
));
$css2 = [];
foreach ($tmp as $x) {
    $css2[] = ['id'=>$x, 'name'=>$x];
}
$tmp = str_replace(US_ROOT_DIR, '', array_merge(
    glob(US_ROOT_DIR.'core/css/*.css'),
    glob(US_ROOT_DIR.'local/css/*.css')
));
$css3 = [];
foreach ($tmp as $x) {
    $css3[] = ['id'=>$x, 'name'=>$x];
}

# Now look up the files in the language dir to allow that choice
$tmp = array_merge(
    glob(US_ROOT_DIR.'core/language/*.php'),
    glob(US_ROOT_DIR.'local/language/*.php')
);
$langs = [];
foreach ($tmp as $x) {
    $l = basename($x);
    if ($l != 'language.php') {
        $langs[] = ['id'=>$l, 'name'=>$l];
    }
}

# Now set up the possible options for the actions to take upon successful save
$multiRowSaveOpts = [
    ['id'=>'1', 'name' => lang('CONTINUE_IN_SAME_PAGE')],
    ['id'=>'2', 'name' => lang('RETURN_TO_BREADCRUMB_PARENT')],
];
$singleRowDelOpts = [
    # can't continue on same page - the row we were looking at is gone
    ['id'=>'2', 'name' => lang('RETURN_TO_BREADCRUMB_PARENT')],
];
$singleRowCreateOpts = [
    ['id'=>'1', 'name' => lang('CONTINUE_IN_SAME_PAGE')],
    ['id'=>'2', 'name' => lang('RETURN_TO_BREADCRUMB_PARENT')],
    ['id'=>'4', 'name' => lang('CREATE_ANOTHER_ROW')],
];
$singleRowEditOpts = [
    ['id'=>'1', 'name' => lang('CONTINUE_IN_SAME_PAGE')],
    ['id'=>'2', 'name' => lang('RETURN_TO_BREADCRUMB_PARENT')],
    ['id'=>'4', 'name' => lang('CREATE_ANOTHER_ROW')], // weird... leave it in?
];

$myForm = new Form ([
    'toc' => new FormField_TabToc(['TocType'=>'tab']),
    'tabs' => new FormTab_Contents([
        'tab_security' => new FormTab_Pane([
            'force_ssl' =>
                new FormField_Select([
                    'dbfield' => 'settings.force_ssl',
                    'display' => lang('SETTINGS_FORCE_SSL'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('YES')],
                        ['id'=>0, 'name'=>lang('NO')],
                    ],
                ]),
            'min_pw_score' =>
                new FormField_Select([
                    'display' => lang('SETTINGS_MIN_PW_SCORE'),
                    'repeat' => [
                        ['id'=>0, 'name'=>lang('PW_VERY_WEAK')],
                        ['id'=>1, 'name'=>lang('PW_WEAK')],
                        ['id'=>2, 'name'=>lang('PW_OK')],
                        ['id'=>3, 'name'=>lang('PW_STRONG')],
                        ['id'=>4, 'name'=>lang('PW_VERY_STRONG')],
                    ],
                    'hint_text' => lang('HINT_MIN_PW_SCORE'),
                ]),
            'recaptcha' =>
                new FormField_Select([
                    'dbfield' => 'settings.recaptcha',
                    'display' => lang('SETTINGS_RECAPTCHA'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('ENABLED')],
                        ['id'=>0, 'name'=>lang('DISABLED')],
                    ],
                ]),
            'recaptcha_private' =>
                new FormField_Text([
                    'dbfield' => 'recaptcha_private',
                    'display' => lang('SETTINGS_RECAPTCHA_PRIVATE_KEY'),
                    'hint_text' => 'Available from Google',
                ]),
            'recaptcha_public' =>
                new FormField_Text([
                    'dbfield' => 'recaptcha_public',
                    'display' => lang('SETTINGS_RECAPTCHA_PUBLIC_KEY'),
                    'hint_text' => 'Available from Google',
                ]),
            'session_timeout' =>
                new FormField_Text([
                    'dbfield' => 'session_timeout',
                    'display' => lang('SETTINGS_SESSION_TIMEOUT'),
                    'new_valid' => [
                        'is_numeric' => true,
                    ],
                    'hint_text' => '3600 = 1 hour; 86400 = 1 day, 604800 = 1 week',
                ]),
            'allow_remember_me' =>
                new FormField_Select([
                    'dbfield' => 'allow_remember_me',
                    'display' => lang('SETTINGS_ALLOW_REMEMBER_ME'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('YES')],
                        ['id'=>0, 'name'=>lang('NO')],
                    ],
                ]),
        ], [
            'title' => lang('SETTINGS_SECURITY_TITLE'),
        ]),
        'tab_css' => new FormTab_Pane ([
            'css_sample' =>
                new FormField_Select([
                    'dbfield' => 'css_sample',
                    'display' => lang('SETTINGS_SHOW_CSS_SAMPLES'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('ENABLED')],
                        ['id'=>0, 'name'=>lang('DISABLED')],
                    ],
                ]),
            'css1' =>
                new FormField_Select([
                    'dbfield' => 'css1',
                    'display' => lang('SETTINGS_CSS1'),
                    'repeat' => $css1,
                ]),
            'css2' =>
                new FormField_Select([
                    'dbfield' => 'css2',
                    'display' => lang('SETTINGS_CSS2'),
                    'repeat' => $css2,
                ]),
            'css3' =>
                new FormField_Select([
                    'dbfield' => 'css3',
                    'display' => lang('SETTINGS_CSS3'),
                    'repeat' => $css3,
                ]),
            'sampleRow' =>
                new Form_Row([
                    'sampleCol' =>
                        new Form_Col([
                        	'<h2>Bootstrap Class Examples (for SAVED settings)</h2>',
                        	'<hr />',
                        	'<button type="button" name="button" class="btn btn-primary">primary</button>',
                        	'<button type="button" name="button" class="btn btn-info">info</button>',
                        	'<button type="button" name="button" class="btn btn-warning">warning</button>',
                        	'<button type="button" name="button" class="btn btn-danger">danger</button>',
                        	'<button type="button" name="button" class="btn btn-success">success</button>',
                        	'<button type="button" name="button" class="btn btn-default">default</button>',
                        	'<hr />',
                        	'<div class="jumbotron"><h1>Jumbotron</h1></div>',
                        	'<div class="well"><p>well</p></div>',
                        	'<h1>This is H1</h1>',
                        	'<h2>This is H2</h2>',
                        	'<h3>This is H3</h3>',
                        	'<h4>This is H4</h4>',
                        	'<h5>This is H5</h5>',
                        	'<h6>This is H6</h6>',
                        	'<p>This is paragraph</p>',
                        	'<a href="#">This is a link</a><br><br>',
                        ], ['col_class' => 'text-center']),
                ], ['delete_if' =>  !configGet('css_sample')]),
        ], [
            'title' => lang("SETTINGS_CSS_TITLE"),
        ]),
        'tab_general' => new FormTab_Pane ([
            'site_name' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_SITE_NAME'),
                    'hint_text' => lang('HINT_SITE_NAME'),
                ]),
            'site_url' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_SITE_URL'),
                    'hint_text' => lang('HINT_SITE_URL'),
                ]),
            'site_language' =>
                new FormField_Select([
                    'display' => lang('SETTINGS_SITE_LANGUAGE'),
                    'hint_text' => lang('HINT_SITE_LANGUAGE'),
                    'repeat' => $langs,
                ]),
            'date_fmt' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_DATE_FMT'),
                    'hint_text' => lang('HINT_SETTINGS_DATE_FMT'),
                ]),
            'time_fmt' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_TIME_FMT'),
                    'hint_text' => lang('HINT_SETTINGS_TIME_FMT'),
                ]),
            'install_location' =>
                new FormField_Text([
                    'dbfield' => 'install_location',
                    'display' => lang('SETTINGS_INSTALL_LOCATION'),
                    'hint_text' => lang('CURRENTLY_UNUSED'),
                ]),
            'copyright_message' =>
                new FormField_Textarea([
                    'dbfield' => 'copyright_message',
                    'display' => lang('SETTINGS_COPYRIGHT_MESSAGE'),
                    'hint_text' => lang('HINT_COPYRIGHT_MESSAGE'),
                    'editable' => true,
                ]),
            'site_offline' =>
                new FormField_Select([
                    'dbfield' => 'site_offline',
                    'display' => lang('SETTINGS_SITE_OFFLINE'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('YES')],
                        ['id'=>0, 'name'=>lang('NO')],
                    ],
                    'hint_text' => lang('HINT_SITE_OFFLINE'),
                ]),
            'debug_mode' =>
                new FormField_Select([
                    'dbfield' => 'debug_mode',
                    'display' => lang('SETTINGS_DEBUG_MODE'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('YES')],
                        ['id'=>0, 'name'=>lang('NO')],
                    ],
                    'hint_text' => lang('HINT_DEBUG_MODE'),
                ]),
            'track_guest' =>
                new FormField_Select([
                    'dbfield' => 'track_guest',
                    'display' => lang('SETTINGS_TRACK_GUESTS'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('YES')],
                        ['id'=>0, 'name'=>lang('NO')],
                    ],
                    'hint_text' => lang('HINT_TRACK_GUESTS'),
                ]),
            'upload_dir' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_UPLOAD_DIR'),
                    'hint_text' => lang('HINT_UPLOAD_DIR'),
                    'valid' => [
                        'regex' => ':^$|\/$:',
                        'regex_display' => lang('REGEX_ENDS_WITH_SLASH'),
                    ],
                ]),
            'upload_allowed_ext' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_UPLOAD_ALLOWED_EXT'),
                    'hint_text' => lang('HINT_UPLOAD_ALLOWED_EXT'),
                ]),
            'upload_max_size' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_UPLOAD_MAX_SIZE'),
                    'hint_text' => lang('HINT_UPLOAD_MAX_SIZE'),
                ]),
        ], [
            'title'=>lang('SETTINGS_GENERAL_TITLE'),
        ]),
        'tab_editor' => new FormTab_Pane ([
            'tinymce_url' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_TINYMCE_URL'),
                    'hint_text' => lang('HINT_TINYMCE_URL'),
                ]),
            'tinymce_apikey' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_TINYMCE_APIKEY'),
                    'hint_text' => lang('HINT_TINYMCE_APIKEY'),
                ]),
            'tinymce_plugins' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_TINYMCE_PLUGINS'),
                    'hint_text' => lang('HINT_TINYMCE_PLUGINS'),
                ]),
            'tinymce_height' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_TINYMCE_HEIGHT'),
                    'hint_text' => lang('HINT_TINYMCE_HEIGHT'),
                ]),
            'tinymce_menubar' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_TINYMCE_MENUBAR'),
                    'hint_text' => lang('HINT_TINYMCE_MENUBAR'),
                ]),
            'tinymce_toolbar' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_TINYMCE_TOOLBAR'),
                    'hint_text' => lang('HINT_TINYMCE_TOOLBAR'),
                ]),
            'tinymce_skin' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_TINYMCE_SKIN'),
                    'hint_text' => lang('HINT_TINYMCE_SKIN'),
                ]),
            'tinymce_theme' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_TINYMCE_THEME'),
                    'hint_text' => lang('HINT_TINYMCE_THEME'),
                ]),
        ], [
            'title'=>lang('SETTINGS_EDITOR_TITLE'),
        ]),
        'tab_redirects' => new FormTab_Pane ([
            'redirect_login' =>
                new FormField_Text([
                    'dbfield' => 'redirect_login',
                    'display' => lang('SETTINGS_REDIRECT_LOGIN'),
                    'hint_text' => lang('HINT_REDIRECT_LOGIN'),
                ]),
            'redirect_logout' =>
                new FormField_Text([
                    'dbfield' => 'redirect_logout',
                    'display' => lang('SETTINGS_REDIRECT_LOGOUT'),
                    'hint_text' => lang('HINT_REDIRECT_LOGOUT'),
                ]),
            'redirect_deny_nologin' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_REDIRECT_DENY_NOLOGIN'),
                    'hint_text' => lang('HINT_REDIRECT_DENY_NOLOGIN'),
                ]),
            'redirect_deny_noperm' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_REDIRECT_DENY_NOPERM'),
                    'hint_text' => lang('HINT_REDIRECT_DENY_NOPERM'),
                ]),
            'redirect_site_offline' =>
                new FormField_Text([
                    'display' => lang('SETTINGS_REDIRECT_SITE_OFFLINE'),
                    'hint_text' => lang('HINT_REDIRECT_SITE_OFFLINE'),
                ]),
            'multi_row_after_create' =>
                new FormField_Select([
                    'display' => lang('SETTINGS_MULTI_ROW_AFTER_CREATE'),
                    'repeat' => $multiRowSaveOpts,
                    'hint_text' => lang('HINT_MULTI_ROW_AFTER_CREATE'),
                ]),
            'multi_row_after_edit' =>
                new FormField_Select([
                    'display' => lang('SETTINGS_MULTI_ROW_AFTER_EDIT'),
                    'repeat' => $multiRowSaveOpts,
                    'hint_text' => lang('HINT_MULTI_ROW_AFTER_EDIT'),
                ]),
            'multi_row_after_delete' =>
                new FormField_Select([
                    'display' => lang('SETTINGS_MULTI_ROW_AFTER_DELETE'),
                    'repeat' => $multiRowSaveOpts,
                    'hint_text' => lang('HINT_MULTI_ROW_AFTER_DELETE'),
                ]),
            'single_row_after_create' =>
                new FormField_Select([
                    'display' => lang('SETTINGS_SINGLE_ROW_AFTER_CREATE'),
                    'repeat' => $singleRowCreateOpts,
                    'hint_text' => lang('HINT_SINGLE_ROW_AFTER_CREATE'),
                ]),
            'single_row_after_edit' =>
                new FormField_Select([
                    'display' => lang('SETTINGS_SINGLE_ROW_AFTER_EDIT'),
                    'repeat' => $singleRowEditOpts,
                    'hint_text' => lang('HINT_SINGLE_ROW_AFTER_EDIT'),
                ]),
            'single_row_after_delete' =>
                new FormField_Select([
                    'display' => lang('SETTINGS_SINGLE_ROW_AFTER_DELETE'),
                    'repeat' => $singleRowDelOpts,
                    'hint_text' => lang('HINT_SINGLE_ROW_AFTER_DELETE'),
                ]),
            'redirect_referrer_login' =>
                new FormField_Select([
                    'dbfield' => 'redirect_referrer_login',
                    'display' => lang('SETTINGS_REDIRECT_REFERRER_LOGIN'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('YES')],
                        ['id'=>0, 'name'=>lang('NO')],
                    ],
                    'hint_text' => lang('HINT_REDIRECT_REFERRER_LOGIN'),
                ]),
        ], [
            'title'=>lang('SETTINGS_REDIRECTS_TITLE'),
        ]),
        'tab_registration' => new FormTab_Pane ([
            'email_act' =>
                new FormField_Select([
                    'dbfield' => 'email_act',
                    'display' => lang('SETTINGS_REQUIRE_EMAIL_VERIFY'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('YES')],
                        ['id'=>0, 'name'=>lang('NO')],
                    ],
                    'hint_text' => lang('HINT_REQUIRE_EMAIL_VERIFY'),
                ]),
            'agreement' =>
                new FormField_Textarea([
                    'dbfield' => 'agreement',
                    'rows' => '10',
                    'display' => lang('SETTINGS_TERMS_AND_CONDITIONS'),
                    'hint_text' => lang('HINT_TERMS_AND_CONDITIONS'),
                ]),
        ], [
            'title'=>lang('SETTINGS_REGISTRATION_TITLE'),
        ]),
        'tab_google' => new FormTab_Pane ([
            'glogin' =>
                new FormField_Select([
                    'dbfield' => 'glogin',
                    'display' => lang('SETTINGS_GLOGIN_STATE'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('ENABLED')],
                        ['id'=>0, 'name'=>lang('DISABLED')],
                    ],
                    'hint_text' => lang('HINT_GLOGIN_STATE'),
                ]),
            'gid' =>
                new FormField_Text([
                    'dbfield' => 'gid',
                    'display' => lang('SETTINGS_GLOGIN_CLIENT_ID'),
                    'hint_text' => lang('HINT_GLOGIN_CLIENT_ID'),
                ]),
            'gsecret' =>
                new FormField_Text([
                    'dbfield' => 'gsecret',
                    'display' => lang('SETTINGS_GLOGIN_SECRET'),
                    'hint_text' => lang('HINT_GLOGIN_SECRET'),
                ]),
            'gcallback' =>
                new FormField_Text([
                    'dbfield' => 'gcallback',
                    'display' => lang('SETTINGS_GLOGIN_CALLBACK'),
                    'hint_text' => lang('HINT_GLOGIN_CALLBACK'),
                ]),
        ], [
            'title'=>lang('SETTINGS_GOOGLE_TITLE'),
        ]),
        'tab_facebook' => new FormTab_Pane ([
            'fblogin' =>
                new FormField_Select([
                    'dbfield' => 'fblogin',
                    'display' => lang('SETTINGS_FBLOGIN_STATE'),
                    'repeat' => [
                        ['id'=>1, 'name'=>lang('ENABLED')],
                        ['id'=>0, 'name'=>lang('DISABLED')],
                    ],
                    'hint_text' => lang('HINT_FBLOGIN_STATE'),
                ]),
            'fbid' =>
                new FormField_Text([
                    'dbfield' => 'fbid',
                    'display' => lang('SETTINGS_FBLOGIN_CLIENT_ID'),
                    'hint_text' => lang('HINT_FBLOGIN_CLIENT_ID'),
                ]),
            'fbsecret' =>
                new FormField_Text([
                    'dbfield' => 'fbsecret',
                    'display' => lang('SETTINGS_FBLOGIN_SECRET'),
                    'hint_text' => lang('HINT_FBLOGIN_SECRET'),
                ]),
            'fbcallback' =>
                new FormField_Text([
                    'dbfield' => 'fbcallback',
                    'display' => lang('SETTINGS_FBLOGIN_CALLBACK'),
                    'hint_text' => lang('HINT_FBLOGIN_CALLBACK'),
                ]),
        ], [
            'title'=>lang('SETTINGS_FACEBOOK_TITLE'),
        ]),
    ]),
    'save' =>
        new FormField_ButtonSubmit ([
            'field' => 'save',
            'display' => lang('SAVE_SITE_SETTINGS'),
        ])
], [
    'table' => 'settings',
    #'debug' => 3,
]);

if (Input::exists()) {
    $settingsData = $db->queryById('settings', 1)->first();
    $myForm->setFieldValues($settingsData);
    $myForm->setNewValues($_POST);
    if ($myForm->updateIfValid(1, $errors)) {
        $successes[] = lang('SETTINGS_UPDATE_SUCCESSFUL');
    }
}

$settingsData = $db->queryById('settings', 1)->first();
$myForm->setFieldValues($settingsData);
$myForm->getField('toc')->setRepData($myForm->getAllFields([], ['class'=>'FormTab_Pane', 'not_only_fields'=>true]));

echo $myForm->getHTML(['errors'=>$errors, 'successes'=>$successes]);
