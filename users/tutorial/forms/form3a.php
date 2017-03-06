<?php
$fooId = Input::get('id');
$db = DB::getInstance();
$fooData = $db->queryById('foo', $fooId)->first();
$myForm = new Form([
    'foo' => new FormField_Text,
    'a' => new FormField_Text,
    'b' => new FormField_Text,
    'bool' => new FormField_Checkbox,
    'save' => new FormField_ButtonSubmit,
], [
    'data' => $fooData,
    'dbtable' => 'foo',
]);
if (Input::exists() && Input::get('save')) {
    $myForm->setNewValues($_POST);
    $myForm->updateIfValid($fooId, $errors);
    $fooData = $db->queryById('foo', $fooId)->first();
    $myForm->setFieldValues($fooData); // reload changed values from database table
}
echo $myForm->getHTML();
