<?php
$myForm = new Form([
    'fname' => new FormField_Text,
    'save' => new FormField_ButtonSubmit,
]);
echo $myForm->getHTML();
