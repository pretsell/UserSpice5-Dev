<?php
if (($fooId = Input::get('id')) || $fooId === '0') {
    $db = DB::getInstance();
    if (!$fooData = $db->queryById('foo', $fooId)->first()) {
        Redirect::to("http://somewhere.else.com");
    }
} else {
    $fooData = [
        'foo' => 'default value for foo',
        'a' => 'default a',
        'b' => 29,
        'bool' => true,
    ];
}
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
if (Input::exists()) {
    $myForm->setNewValues($_POST);
    if ($fooId || $fooId === '0') {
        $myForm->updateIfValid($fooId, $errors);
    } else {
        $fooId = $myForm->insertIfValid($errors);
    }
    $fooData = $db->queryById('foo', $fooId)->first();
    $myForm->setFieldValues($fooData); // reload changed values from database table
}
echo $myForm->getHTML();
