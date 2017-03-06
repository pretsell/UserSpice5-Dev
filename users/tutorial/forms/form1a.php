<?php
$myForm = new Form([
    'name' => new FormField_Text,
    'save' => new FormField_ButtonSubmit,
]);
echo $myForm->getHTML();
