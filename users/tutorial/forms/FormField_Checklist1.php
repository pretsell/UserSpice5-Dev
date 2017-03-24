<?php
$db = DB::getInstance();
$fooData = $db->queryAll('foo')->results(); // contains columns `id` and `foo`
$myForm = new Form([
    'delete' => new FormField_Checklist([
        'data' => $fooData,
        #'th_row' => '<th>Mark for Deletion</th><th>Foo</th>',
        #'td_row' => '<td>{CHECKBOX_ID}</td><td>{FOO}</td>',
        #'checkbox_label' => 'Mark for Deletion',
    ]),
    'delete_button' => new FormField_ButtonDelete([
        'display' => 'Delete Marked Rows',
    ]),
], [
    'autoshow' => 1,
]);

if (Input::exists()) {
    echo "This is what the \$_POST looks like:";
    var_dump($_POST);
    echo "<br />\nIf `'default'=>'processing'` had been set for the form then any rows matching the IDs listed in the `delete` array (above) would have been automatically deleted.";
}
