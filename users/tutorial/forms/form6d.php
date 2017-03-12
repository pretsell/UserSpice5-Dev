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
    'toc' => new FormField_TabToc(),
    new FormTab_Contents([
        'tabA' => new FormTab_Pane([
            'foo' => new FormField_Text,
            'a' => new FormField_Text,
        ], [
            'active_tab' => 'active',
            'tab_id'=>'tabA',
            'title'=>'Tab A Title',
        ]),
        'tabB' => new FormTab_Pane([
            'b' => new FormField_Text,
        ], [
            'tab_id'=>'tabB',
            'title'=>'Tab B Title',
        ]),
        'tabBool' => new FormTab_Pane([
            'bool' => new FormField_Checkbox,
        ], [
            'tab_id'=>'tabBool',
            'title'=>'Tab Bool Title',
        ]),
    ]),
    'save' => new FormField_ButtonSubmit,
], [
    'data' => $fooData,
    'dbtable' => 'foo',
]);
$myForm->getField('toc')->setRepData(
    $myForm->getAllFields([], ['class'=>'FormTab_Pane', 'not_only_fields'=>true])
);
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
