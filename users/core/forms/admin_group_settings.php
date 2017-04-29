<?php
$mode = 'GROUP';
if ($user->isAdmin() && $groupId = Input::get('group_id')) {
    $gd = fetchGroupDetails($groupId);
    $lang['ADMIN_SETTINGS_TITLE'] = lang('ADMIN_SETTINGS_PER_GROUP_TITLE', $ud->name);
    $groupQ = $db->query("SELECT * FROM $T[settings] settings WHERE group_id = ?", [$groupId]);
    $db->errorSetMessage($errors);
    if ($groupQ->count() > 0) {
        $groupR = $groupQ->first();
        $_GET['id'] = $groupR->id;
    } else {
        $db->insert('settings', ['group_id' => $groupId]);
        $_GET['id'] = $db->lastId();
    }
} else {
    if (!$user->isAdmin()) {
        $errors[] = "ERROR: Must be an administrator";
    } else {
        $errors[] = "ERROR: group_id not set in URL";
    }
}
$_REQUEST['id'] = $_GET['id'];
include_once(pathFinder('forms/admin_settings.php'));
