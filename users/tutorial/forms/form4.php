<?php
#var_dump($_POST);
$fooId = @$_GET['id'];
$myForm = new Form([
    'foo' => new FormField_Text,
    'a' => new FormField_Text,
    'b' => new FormField_Text,
    'save' => new FormField_ButtonSubmit,
], [
    'title' => 'This is a tutorial title ('.basename($_SERVER['PHP_SELF']).')',
    'dbtable' => 'foo',
    'autoload' => true,
    'autoloadposted' => $_POST,
    'autosave' => true,
]);
echo $myForm->getHTML();
