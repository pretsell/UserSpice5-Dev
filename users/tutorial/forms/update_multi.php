<?php
/*
 * update_multi.php
 */

#var_dump($_POST);
$myForm = new Form([
    new Form_Row([
        new Form_Col([
            '<h4>UPDATE rows in FOO</h4>',
            'updFoo' => new FormField_Table([
                #'debug' => 4,
                'th_row' => [
                    'Id',
                    'Foo',
                    'A',
                    'B'
                ],
                'td_row' => [
                    '{ID}',
                    '{HIDDEN_ID}{foo(TEXT)}',
                    '{a(TEXT)}',
                    '{b(TEXT)}',
                ],
                #'sql_cols' => ['id, foo'],
                #'sql_from' => 'foo',
                #'sql_where' => '1=1',
                'sql' => 'SELECT id, foo, a, b FROM foo',
            ], [
                'action' => 'update',
                'button' => 'updateMulti',
                'fields' => ['foo' => '{foo}', 'a' => '{a}', 'b' => '{b}'],
            ]),
        ], [
            'Col_Class' => 'col-xs-12 col-md-6',
        ]),
    ]),
    'updateMulti' => new FormField_ButtonSubmit([
        'display' => 'Update Foo'
    ]),
], [
    'dbtable' => 'foo',
    'autoload'=>true,
    'autosave'=>true,
    'autoshow'=>true,
    #'autoredirect'=>false,
    'multirow' => true, // for redirect rules
    #'debug' => 4,
]);
