<?php
/*
 * delete_multi.php
 */

$myForm = new Form([
    new Form_Row([
    new Form_Col([
        '<h4>Select rows below to DELETE from FOO</h4>',
        'delFoo' => new FormField_Table([
            #'debug' => 4,
            'th_row' => ['Mark to Delete', 'Name'],
            'td_row' => ['{CHECKBOX_ID}', '{name}'],
            #'sql_cols' => ['id, foo'],
            #'sql_from' => 'foo',
            #'sql_where' => '1=1',
            'sql' => 'SELECT id, foo FROM foo',
        ]),
    ], [
        'Col_Class' => 'col-xs-12 col-md-6',
    ]),
    new Form_Col([
        '<h4>Select rows below to INSERT these rows from BAR into FOO</h4>',
        'insFoo' => new FormField_Table([
            #'debug' => 4,
            'th_row' => ['Mark to Add to FOO', 'Name'],
            'td_row' => ['{CHECKBOX_ID}', '{name}'],
            #'sql_cols' => ['id, foo'],
            #'sql_from' => 'foo',
            #'sql_where' => '1=1',
            'sql' => 'SELECT id, bar FROM bar',
        ]),
        'dataBar' => new FormField_MultiHidden([
            'sql' => 'SELECT id, bar, a FROM bar',
            'field' => 'dataBar',
            'sql_cols' => ['id', 'bar', 'a'],
        ])
    ], [
        'Col_Class' => 'col-xs-12 col-md-6',
    ]),
    ]),
    'deleteMulti' => new FormField_ButtonDelete([
        'display' => 'Delete from Foo'
    ]),
    'insertMulti' => new FormField_ButtonSubmit([
        'display' => 'Insert into Foo'
    ]),
], [
    'dbtable' => 'foo',
    'autoload'=>true,
    'autosave'=>true,
    'autoshow'=>true,
    #'autoredirect'=>false,
    'multirow' => true, // make sure we redirect according to multi-row config
    #'title'=>'Deleting from Multi-Table',
    'save_multi_button' => 'insertMulti',
    'insert_multi_row_fields' => ['foo'=>'{bar}', 'a'=>'{a}'],
    'insert_multi_rows_key' => 'insFoo',
    'delete_multi_button' => 'deleteMulti',
    'delete_multi_row_where' => ['id' => '{delFoo}'],
    #'debug' => 4,
]);
