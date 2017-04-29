<?php
if ($messageId = Input::get('id')) {
    // first set the status to "read"
    $db->query("UPDATE $T[addressees] SET is_read = 1 WHERE user_id = ? AND message_id = ?", [$user->id(), $messageId]);
    // now read in the recipients and format them
    $to = $db->query("SELECT username, is_read FROM $T[addressees] a JOIN $T[users] users ON (a.user_id = users.id) WHERE message_id = ?", [$messageId])->results();
    $sep = $addressees = '';
    foreach ($to as $t) {
        if (!$t->is_read) {
            $addressees .= $sep.'<strong>'.$t->username.'</strong>';
        } else {
            $addressees .= $sep.$t->username;
        }
        $sep = ', ';
    }
} else {
    $messageId = 0;
    $addressees = '';
}
if (isset($user) && $user->isLoggedIn()) {
    $userId = $user->id();
} else {
    $errors[] = 'Must be logged in';
    Redirect::to(configGet('redirect_deny_nologin'));
}
$myForm = new Form([
    /*
    'searchQ' => new FormField_SearchQ([
        'deleteif' => $messageId
    ]),
    */
    new Form_Panel([ // need row because col-md-9 and col-lg-6 allow subject to move up beside
        'recipients' => new FormField_Table([
            'th_row' => [
                lang('MESSAGES_MARK_RECIPIENTS'),
                lang('USERNAME'),
                lang('MESSAGES_NAME'),
            ],
            'td_Row' => [
                '{CHECKBOX_ID}',
                '{USERNAME}',
                '{NAME}',
            ],
            'sql' => "SELECT id, username, concat(fname, ' ', lname) as name
                        FROM $T[users]
                        ORDER BY username",
            #'placeholder_row' => [ 'id' => '', 'username' => 'Please choose a recipient'],
            #'searchable' => true,
            'readonly' => $messageId,
            'isdbfield' => false,
            'deleteif' => $messageId,
            #'Div_Class' => 'col-xs-12 col-md-9 col-lg-6', // helps tighten table
            'datatables' => '{ "scrollY": "100px", "paging": false, info: false, }',
        ], [
            'action' => 'insert',
            'dbtable' => 'addressees',
            'fields' => [
                'user_id' => '{ID}',
                'message_id' => '{messages.LAST_ID}',
                'is_read' => 0,
                'folder_id' => 0,
            ]
        ]),
        'addr_display' => new FormField_HTML([
            'display' => lang('MESSAGES_RECIPIENTS'),
            'value' => $addressees,
            'deleteif' => !$messageId,
        ]),
    ], [
        'head' => lang("MESSAGES_RECIPIENTS"),
    ]),
    'sender_id' => new FormField_Hidden([
        'value' => $user->id(),
    ]),
    'subject' => new FormField_Text([
        'display' => lang('MESSAGES_SUBJECT'),
        'readonly' => $messageId,
        'required' => true,
    ]),
    'message' => new FormField_Textarea([
        'display' => lang('MESSAGES_MESSAGE'),
        'readonly' => $messageId,
    ]),
    /*
    'msg_display' => new FormField_HTML([
        'display' => lang('MESSAGES_MESSAGE'),
        'dbfield' => 'message',
        'Input' => '<div style="border: thin solid black">{VALUE}</div>',
        'isdbfield' => true, // force to load
        #'debug' => 5,
        'delete_if' => !$messageId,
    ]),
    */
    'save' => new FormField_ButtonSubmit([
        'display' => lang('MESSAGES_SEND'),
        'delete_if' => $messageId,
    ]),
    'goInbox' => new FormField_ButtonAnchor([
        'display' => lang('MESSAGES_GO_INBOX'),
        'href' => 'inbox.php',
    ]),
    'goSentmail' => new FormField_ButtonAnchor([
        'display' => lang('MESSAGES_GO_SENTMAIL'),
        'href' => 'sentmail.php',
    ]),
], [
    'dbtable' => 'messages',
    'default' => 'process',
    'autoshow' => true,
    #'headersnippet' => '<style>#div-recipients { border: solid black; margin: 10px; padding: 10px }</style>',
]);
#echo $myForm->getHTML();
