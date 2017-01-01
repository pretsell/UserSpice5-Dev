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
    glob(US_ROOT_DIR.'us_core/css/color_schemes/*.css'),
    glob(US_ROOT_DIR.'local/css/color_schemes/*.css')
));
$css1 = [];
foreach ($tmp as $x) {
    $css1[] = ['id'=>$x, 'name'=>$x];
}
$tmp = str_replace(US_ROOT_DIR, '', array_merge(
    glob(US_ROOT_DIR.'us_core/css/*.css'),
    glob(US_ROOT_DIR.'local/css/*.css')
));
$css2 = [];
foreach ($tmp as $x) {
    $css2[] = ['id'=>$x, 'name'=>$x];
}
$tmp = str_replace(US_ROOT_DIR, '', array_merge(
    glob(US_ROOT_DIR.'us_core/css/*.css'),
    glob(US_ROOT_DIR.'local/css/*.css')
));
$css3 = [];
foreach ($tmp as $x) {
    $css3[] = ['id'=>$x, 'name'=>$x];
}

# Now look up the files in the language dir to allow that choice
$tmp = array_merge(
    glob(US_ROOT_DIR.'us_core/language/*.php'),
    glob(US_ROOT_DIR.'local/language/*.php')
);
$langs = [];
foreach ($tmp as $x) {
    $l = basename($x);
    if ($l != 'language.php') {
        $langs[] = ['id'=>$l, 'name'=>$l];
    }
}

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
            'active_tab' => 'active',
            'tab_id' => 'tab_security',
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
            'tab_id' => 'tab_css',
            'title' => lang("SETTINGS_CSS_TITLE"),
        ]),
        'tab_general' => new FormTab_Pane ([
            'site_name' =>
                new FormField_Text([
                    'dbfield' => 'site_name',
                    'display' => lang('SETTINGS_SITE_NAME'),
                    'hint_text' => lang('HINT_SITE_NAME'),
                ]),
            'site_url' =>
                new FormField_Text([
                    'dbfield' => 'site_url',
                    'display' => lang('SETTINGS_SITE_URL'),
                    'hint_text' => lang('HINT_SITE_URL'),
                ]),
            'site_language' =>
                new FormField_Select([
                    'dbfield' => 'site_language',
                    'display' => lang('SETTINGS_SITE_LANGUAGE'),
                    'repeat' => $langs,
                ]),
            'install_location' =>
                new FormField_Text([
                    'dbfield' => 'install_location',
                    'display' => lang('SETTINGS_INSTALL_LOCATION'),
                    'hint_text' => lang('CURRENTLY_UNUSED'),
                ]),
            'copyright_message' =>
                new FormField_Text([
                    'dbfield' => 'copyright_message',
                    'display' => lang('SETTINGS_COPYRIGHT_MESSAGE'),
                    'hint_text' => lang('HINT_COPYRIGHT_MESSAGE'),
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
        ], [
            'tab_id'=>'tab_general',
            'title'=>lang('SETTINGS_GENERAL_TITLE'),
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
                    'dbfield' => 'redirect_deny_nologin',
                    'display' => lang('SETTINGS_REDIRECT_DENY_NOLOGIN'),
                    'hint_text' => lang('HINT_REDIRECT_DENY_NOLOGIN'),
                ]),
            'redirect_deny_noperm' =>
                new FormField_Text([
                    'dbfield' => 'redirect_deny_noperm',
                    'display' => lang('SETTINGS_REDIRECT_DENY_NOPERM'),
                    'hint_text' => lang('HINT_REDIRECT_DENY_NOPERM'),
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
            'tab_id'=>'tab_redirects',
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
            'tab_id'=>'tab_registration',
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
            'tab_id'=>'tab_google',
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
            'tab_id'=>'tab_facebook',
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
    if ($myForm->updateIfChangedAndValid(1, $errors)) {
        $successes[] = lang('SETTINGS_UPDATE_SUCCESSFUL');
    }
}

$settingsData = $db->queryById('settings', 1)->first();
$myForm->setFieldValues($settingsData);
$myForm->getField('toc')->setRepData($myForm->getAllFields([], ['class'=>'FormTab_Pane', 'not_only_fields'=>true]));

echo $myForm->getHTML(['errors'=>$errors, 'successes'=>$successes]);
