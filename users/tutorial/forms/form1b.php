<?php
$myForm = new Form ([
    'name' => new FormField_Text([
        'display' => 'Field Name:',
    ]),
    'save' => new FormField_ButtonSubmit([
        'display' => 'Save The Baby Whales',
    ]),
]);
echo $myForm->getHTML();
