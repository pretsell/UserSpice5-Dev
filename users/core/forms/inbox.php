<?php
if (isset($user) && $user->isLoggedIn()) {
    $userId = $user->id();
} else {
    $errors[] = 'Must be logged in';
    Redirect::to(configGet('redirect_deny_nologin'));
}
/*
if (($read = Input::get('markRead')) || Input::get('markUnread')) {
    if ($read) {
        $read = 1;
    } else {
        $read = 0;
    }
    $fields = [ 'is_read' => $read ];
    foreach (Input::get('marked_msgs') as $msg_id) {
        $db->updateById('addressees', $msg_id, $fields);
    }
}
*/
$myForm = new Form([
    'marked_msgs' => new FormField_Table([
        'th_row' => [
            '{'.lang('CHECK_ALL').'(checkallbox)}',
            lang('MESSAGES_SUBJECT'),
            lang('MESSAGES_FROM'),
            lang('MESSAGES_SENT_DATE'),
            lang('MESSAGES_UNREAD'),
        ],
        'td_row' => [
            '{CHECKBOX_ID}',
            '<a href="message.php?id={message_id}" >{subject}</a>',
            '{username}',
            '{sent}',
            '{unread}',
        ],
        'sql' => "SELECT addressees.id, messages.subject, addressees.message_id,
                        senders.username, sent, addressees.is_read
                    FROM $T[addressees] addressees
                    JOIN $T[messages] messages ON (addressees.message_id = messages.id)
                    JOIN $T[users] senders ON (messages.sender_id = senders.id)
                    WHERE addressees.user_id = ? ",
        'bindvals' => [$userId],
        'postfunc' => 'fmtSentDate',
    ], [
        'action' => 'delete',
        'button' => 'delete',
        'dbtable' => 'addressees',
    ]),
    'delete' => new FormField_ButtonSubmit([
        'display' => lang('MESSAGES_DELETE_MARKED'),
    ]),
    'markRead' => new FormField_ButtonSubmit([
        'display' => lang('MESSAGES_MARK_MARKED_AS_READ'),
    ]),
    'markUnread' => new FormField_ButtonSubmit([
        'display' => lang('MESSAGES_MARK_MARKED_AS_UNREAD'),
    ]),
    'sendNew' => new FormField_ButtonAnchor([
        'display' => lang('MESSAGES_SEND_NEW'),
        'href' => 'message.php', // without id=n it adds a new one
    ]),
], [
    'dbtable' => 'addressees',
    'default' => 'process',
    'multiRead' => [
        'action' => 'update',
        'idfield' => 'marked_msgs',
        'fields' => [ 'is_read' => true ],
        'button' => 'markRead',
    ],
    'multiUnread' => [
        'action' => 'update',
        'idfield' => 'marked_msgs',
        'fields' => [ 'is_read' => false ],
        'button' => 'markUnread',
    ],
]);

function fmtSentDate(&$rows, $junk) {
    $dateFmt = configGet('date_fmt');
    $timeFmt = configGet('time_fmt');
    foreach ($rows as &$row) {
        #dbg($row['sent']);
        #$row['sent'] = date($dateFmt.' '.$timeFmt, $row['sent']);
        $row['unread'] = ($row['is_read'] ? '' : 'X');
    }
}
