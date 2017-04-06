<?php
$myForm = new Form([
    'foo' => new FormField_Textarea(),
    'save' => new FormField_ButtonSubmit([
        'display' => 'Save Changes',
    ]),
], [
    'default' => 'processing',
    'dbtable' => 'foo',
    'autoshow' => true,
]);

if (Input::exists()) {
    echo "This is what the \$_POST looks like:";
    var_dump($_POST);
    echo "<br />\nIf `'default'=>'processing'` had been set for the form then any rows matching the IDs listed in the `delete` array (above) would have been automatically deleted.";
}
