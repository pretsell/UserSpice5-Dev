<?php
/*
 * delete_multi.php
 */

$myForm = new Form([
    '<h4>Some random fields from <strong>foo</strong> table</h4>',
    'foo' => new FormField_Text([
        'display' => 'Foo column',
    ]),
    'a' => new FormField_Text([
        'display' => 'A column',
    ]),
    'b' => new FormField_Text([
        'display' => 'B column',
    ]),
    'save' => new FormField_ButtonSubmit,
    'delete' => new FormField_ButtonDelete,
    'insert' => new FormField_ButtonAnchor([
        'dest' => $_SERVER['PHP_SELF'],
    ]),
    new Form_Panel([
        'delFoo' => new FormField_Table([
            'th_row' => ['Mark to Delete', 'Name'],
            'td_row' => ['{CHECKBOX_ID}', '{name}'],
            'sql' => 'SELECT id, foo FROM foo ORDER BY foo',
        ]),
        'deleteMulti' => new FormField_ButtonDelete([
            'display' => 'Delete from Foo'
        ]),
    ], [
        'head' => 'Select rows below to DELETE from FOO',
        'Panel_Class' => 'panel-default col-xs-12 col-md-6',
        'dbtable' => 'foo',
        'autoload'=>true,
        'autosave'=>true,
        'multirow' => true, // make sure we redirect according to multi-row config
        'delete_multi_button' => 'deleteMulti',
        'delete_multi_row_where' => ['id' => '{delFoo}'],
    ]),
    new Form_Panel([
        'insFoo' => new FormField_Table([
            'th_row' => ['Mark to Add to FOO', 'Name'],
            'td_row' => ['{CHECKBOX_ID}', '{name}'],
            'sql' => 'SELECT id, bar FROM bar',
        ]),
        'dataBar' => new FormField_MultiHidden([
            'sql' => 'SELECT id, bar, a FROM bar ORDER BY bar',
            'field' => 'dataBar',
            'sql_cols' => ['id', 'bar', 'a'],
            'prefix' => 'dataBar-',
        ]),
        'insertMulti' => new FormField_ButtonSubmit([
            'display' => 'Insert into Foo'
        ]),
    ], [
        'head' => 'Select rows below to INSERT these rows from BAR into FOO',
        'Panel_Class' => 'panel-default col-xs-12 col-md-6',
        'dbtable' => 'foo',
        'autoload'=>true,
        'autosave'=>true,
        'multirow' => true, // make sure we redirect according to multi-row config
        'save_multi_button' => 'insertMulti',
        'insert_multi_row_fields' => ['foo'=>'{dataBar-bar}', 'a'=>'{dataBar-a}'],
        'insert_multi_rows_key' => 'insFoo',
    ]),
], [
    'dbtable' => 'foo',
    'autoload'=>true,
    'autoloadposted'=>true,
    'autosave'=>true,
    'autoshow'=>true,
    #'autoredirect'=>false,
    #'multirow' => true, // make sure we redirect according to multi-row config
    #'title'=>'Deleting from Multi-Table',
    #'save_multi_button' => 'insertMulti',
    #'insert_multi_row_fields' => ['foo'=>'{bar}', 'a'=>'{a}'],
    #'insert_multi_rows_key' => 'insFoo',
    #'delete_multi_button' => 'deleteMulti',
    #'delete_multi_row_where' => ['id' => '{delFoo}'],
    'debug' => -1,
]);
