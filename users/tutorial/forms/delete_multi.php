<?php
/*
 * delete_multi.php
 */

$myForm = new Form([
    'delFoo' => new FormField_Table([
        #'debug' => 4,
        'th_row' => ['Mark to Delete', 'Name'],
        'td_row' => ['{CHECKBOX_ID}', '{name}'],
        #'sql_cols' => ['id, foo'],
        #'sql_from' => 'foo',
        #'sql_where' => '1=1',
        'sql' => 'SELECT id, foo FROM foo',
    ]),
    'deleteMulti' => new FormField_ButtonDelete,
], [
    'dbtable' => 'foo',
    'autoload'=>true,
    'autosave'=>true,
    'autoshow'=>true,
    #'autoredirect'=>false,
    'multirow' => true, // make sure we redirect according to multi-row config
    #'title'=>'Deleting from Multi-Table',
    'delete_multi_button' => 'deleteMulti',
    'delete_multi_row_where' => ['id' => '{delFoo}'],
    #'debug' => 4,
]);
