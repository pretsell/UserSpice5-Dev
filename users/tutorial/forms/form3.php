<?php
$fooId = @$_GET['id'];
$myForm = new Form([
    'foo' => new FormField_Text([
        'display' => 'Foo:',
    ]),
    'a' => new FormField_Text([
        'display' => 'A:',
    ]),
    'b' => new FormField_Text([
        'display' => 'B:',
    ]),
    'save' => new FormField_ButtonSubmit([
        'display' => 'Save',
    ]),
], [
    'title' => 'This is a tutorial title ('.basename($_SERVER['PHP_SELF']).')',
    'dbtableId' => $fooId,
    'dbtable' => 'foo',
    'autoload' => true,
]);
echo $myForm->getHTML();
