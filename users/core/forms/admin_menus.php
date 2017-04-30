<?php
if (Input::get('createButton')) {
    Redirect::to('admin_menu.php', 'menu_title='.Input::get('new_menu_title'));
}
$myForm = new Form([
    'menu_title' => new FormField_Table([
        'th_row' => ['Menu Set', 'Menu Item Count'],
        'td_row' => ['<a href="admin_menu.php?menu_title={menu_title}">{menu_title}</a>', '{menu_item_count}'],
        'sql' => "SELECT menu_title, COUNT(*) AS menu_item_count FROM $T[menus] GROUP BY menu_title",
    ]),
    'createWell' => new Form_Well([
        'new_menu_title' => new FormField_Text([
            'display' => lang('ADMIN_MENUS_NEW_MENU_NAME'),
        ]),
        'createButton' => new FormField_ButtonSubmit([
            'display' => lang('CREATE'),
        ]),
    ], [
        'title' => lang('ADMIN_MENUS_CREATE_MENU_TITLE'),
    ])
], [
    'autoload' => true,
    'autoshow' => true,
    'Keep_AdminDashBoard' => true,
]);
