<?php

$mode = 'USER';

# You can always update your own user settings, but only admin can specify user_id
if (!$user->isAdmin() || !$userId = Input::get('user_id')) {
    $userId = $user->id();
}

# get the username and set the title
$ud = fetchUserDetails(null, null, $userId);
$lang['ADMIN_SETTINGS_TITLE'] = lang('ADMIN_SETTINGS_PER_USER_TITLE', $ud->username);

# make sure we have the settings.id in $_GET['id']
$userQ = $db->query("SELECT * FROM $T[settings] settings WHERE user_id = ?", [$userId]);
$db->errorSetMessage($errors);
if ($userQ->count() > 0) {
    $userR = $userQ->first();
    $_GET['id'] = $userR->id;
} else {
    $db->insert('settings', ['user_id' => $userId]);
    $_GET['id'] = $db->lastId();
}
$_REQUEST['id'] = $_GET['id'];

# Now call the actual form
include_once(pathFinder('forms/admin_settings.php'));
