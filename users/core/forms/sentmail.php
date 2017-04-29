<?php
if (isset($user) && $user->isLoggedIn()) {
    $userId = $user->id();
} else {
    $errors[] = 'Must be logged in';
    Redirect::to(configGet('redirect_deny_nologin'));
}
$myForm = new Form([
    'marked_msgs' => new FormField_Table([
        'th_row' => [
            '{'.lang('CHECK_ALL').'(checkallbox)}',
            lang('MESSAGES_SUBJECT'),
            lang('MESSAGES_TO'),
            lang('MESSAGES_SENT_DATE'),
        ],
        'td_row' => [
            '{CHECKBOX_ID}',
            '<a href="message.php?id={id}" >{subject}</a>',
            '{recipients}',
            '{sent}',
        ],
        'sql' => "SELECT messages.id, messages.subject, messages.sent
                    FROM $T[messages] messages
                    WHERE messages.sender_id = ? ",
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
    'sendNew' => new FormField_ButtonAnchor([
        'display' => lang('MESSAGES_SEND_NEW'),
        'href' => 'message.php', // without id=n it adds a new one
    ]),
], [
    'dbtable' => 'messages',
    'default' => 'process',
]);

function fmtSentDate(&$rows, $junk) {
    global $T;
    $db = DB::getInstance();
    $dateFmt = configGet('date_fmt');
    $timeFmt = configGet('time_fmt');
    $sql = "SELECT username, is_read
            FROM $T[addressees] a
            JOIN $T[users] u ON (u.id = a.user_id)
            WHERE a.message_id = ?";
    foreach ($rows as &$row) {
        #dbg($row['sent']);
        #$row['sent'] = date($dateFmt.' '.$timeFmt, $row['sent']);
        #$row['unread'] = ($row['is_read'] ? '' : 'X');
        $addrs = $db->query($sql, [$row['id']])->results(true);
        $recip = $sep = '';
        foreach ($addrs as $addr) {
            $recip .= $sep . ($addr['is_read'] ? '' : '<strong>') .
                $addr['username'] . ($addr['is_read'] ? '' : '</strong>');
            $sep = ', ';
        }
        $row['recipients'] = $recip;
    }
}
