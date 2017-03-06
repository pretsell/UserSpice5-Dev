<?php
#var_dump($_POST);
$fooId = @$_GET['id'];
$myForm = new Form([
], [
    'title' => 'This is a tutorial title ('.basename($_SERVER['PHP_SELF']).')',
    'dbtable' => 'foo',
    'default_everything' => true,
]);
echo $myForm->getHTML();
