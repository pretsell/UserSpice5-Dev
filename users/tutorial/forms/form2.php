<?php
$myForm = new Form([
    'fname' => new FormField_Text([
        'display' => 'Name:',
    ]),
    'save' => new FormField_ButtonSubmit([
        'display' => 'Save',
    ]),
]);
echo $myForm->getHTML();
