<?php
#var_dump($_POST);
$fooId = @$_GET['id'];
$myForm = new Form([
    'foo' => new FormField_Text([
        'valid' => ['max'=>5],
    ]),
    'a' => new FormField_Text([
        'valid' => ['required'=>true, 'max'=>93],
    ]),
    'b' => new FormField_Text([
        'valid' => ['is_numeric', 'min_val'=>3, 'max_val'=>10],
    ]),
    'save' => new FormField_ButtonSubmit,
], [
    'title' => 'This is a tutorial title ('.basename($_SERVER['PHP_SELF']).')',
    #'errors' => &$errors,
    #'successes' => &$successes,
    'dbtableid' => $fooId,
    'dbtable' => 'foo',
    'autoload' => true,
    'autoloadposted' => $_POST,
    'autosave' => true,
]);
echo $myForm->getHTML();
