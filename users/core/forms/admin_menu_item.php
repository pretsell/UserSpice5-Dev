<?php
checkToken();

if (!Input::get('id') && !Input::get('menu_title')) {
    $errors[] = "ERROR: Must specify either menu ID (for editing) or menu title (for adding)";
    Redirect::to('admin_menus.php');
} elseif ($menuId = Input::get('id')) {
    $row = $db->queryById('menus', $menuId)->first();
    if (!$row) {
        $errors[] = "ERROR: Specified ID ($menuId) does not exist";
        Redirect::to('admin_menus.php');
    }
    $menuTitle = $row->menu_title;
} else { // creating a new one
    $menuId = null;
    $menuTitle = Input::get('menu_title');
}

$addSQL = "SELECT id, name
            FROM $T[groups] groups
            WHERE NOT EXISTS
                (SELECT *
                 FROM $T[groups_menus] gm
                 WHERE gm.group_id = groups.id
                   AND gm.menu_id = ?) ";
$myForm = new Form([
    'toc' => new FormField_TabToc(),
    'contents' => new FormTab_Contents([
        'generalPane' => new FormTab_Pane([
            'menu_title' => new FormField_Text([
                'display' => lang('ADMIN_MENU_MENU_TITLE'),
                'readonly' => true,
                'value' => $menuTitle,
            ]),
            'label_token' => new FormField_Text([
                'display' => lang('ADMIN_MENU_LABEL_TOKEN'),
            ]),
            lang('ADMIN_MENU_ICON_CLASS_LINK'),
            'icon_class' => new FormField_Text([
                'display' => lang('ADMIN_MENU_ICON_CLASS'),
                'hint_text' => lang('ADMIN_MENU_ICON_CLASS_HINT')
            ]),
            'icon_label' => new FormField_HTML([
                'display' => lang('ADMIN_MENU_ICON_LABEL'),
                'value' => '<span class="'.
                    (isset($_POST['icon_class']) ?
                        $_POST['icon_class'] : @$row->icon_class).
                    '"></span>&nbsp;'.
                    lang(isset($_POST['label_token']) ?
                        $_POST['label_token'] : @$row->label_token),
                'delete_if' => empty($menuId),
            ]),
            'parent' => new FormField_Select([
                'display' => lang('ADMIN_MENU_PARENT'),
                'sql' => "SELECT id, label_token,
                            LPAD(display_order, 5, '0') as sort_order
                          FROM $T[menus]
                          WHERE menu_title = ?
                            AND parent = -1
                          UNION
                          SELECT menus.id, concat('>>>&nbsp;', menus.label_token),
                            concat(LPAD(m2.display_order, 5, '0'), '-', LPAD(menus.display_order, 5, '0'))
                          FROM $T[menus] menus
                          JOIN $T[menus] m2 ON (menus.parent = m2.id)
                          WHERE menus.menu_title = ?
                            AND menus.parent > 0
                          ORDER BY sort_order
                            ",
                'bindvals' => [$menuTitle, $menuTitle],
                'placeholder_row' => ['id' => '-1', 'name' => lang('ADMIN_MENU_TOP_LEVEL')],
                'value' => -1, // default on create, loaded from data on modify
            ]),
            'display_order' => new FormField_Text([
                'display' => lang('ADMIN_MENU_DISPLAY_ORDER'),
            ]),
            'page_id' => new FormField_Select([
                'display' => lang('ADMIN_MENU_PAGE'),
                'placeholder_row' => ['id' => '', 'name' => lang('ADMIN_MENU_NO_PAGE_ASSIGNED')],
                'sql' => "SELECT id, page FROM $T[pages] ORDER BY page",
            ]),
            'link' => new FormField_Text([
                'display' => lang('ADMIN_MENU_LINK'),
            ]),
            'link_target' => new FormField_Select([
                'display' => lang('ADMIN_MENU_LINK_TARGET'),
                'hint_text' => lang('ADMIN_MENU_LINK_TARGET_HINT'),
                'data' => [
                    ['id' => '_self',  'name' => lang('ADMIN_MENU_LINK_TARGET_SELF')],
                    ['id' => '_blank', 'name' => lang('ADMIN_MENU_LINK_TARGET_BLANK')],
                    ['id' => '_parent','name' => lang('ADMIN_MENU_LINK_TARGET_PARENT')],
                    ['id' => '_top',   'name' => lang('ADMIN_MENU_LINK_TARGET_TOP')],
                ],
                'value' => '_self', // default on create, loaded from data on modify
            ]),
        ]),
        'accessPane' => new FormTab_Pane([
            'logged_in' => new FormField_Select([
                'display' => lang('ADMIN_MENU_LOGGED_IN'),
                'hint_text' => lang('ADMIN_MENU_LOGGED_IN_HINT'),
                'data' => [
                    ['id' => '-1', 'name' => lang('ADMIN_MENU_ITEM_LOGGED_IN_CANNOT')],
                    ['id' => '0', 'name' => lang('ADMIN_MENU_ITEM_LOGGED_IN_EITHER')],
                    ['id' => '1', 'name' => lang('ADMIN_MENU_ITEM_LOGGED_IN_MUST')],
                ],
                'value' => 0, // default on create, loaded from data on modify
            ]),
            'admin' => new FormField_Select([
                'display' => lang('ADMIN_MENU_ADMIN'),
                'hint_text' => lang('ADMIN_MENU_ADMIN_HINT'),
                'data' => [
                    # this doesn't make sense - admin priv overrides!
                    #['id' => '-1', 'name' => lang('ADMIN_MENU_ITEM_ADMIN_CANNOT')],
                    ['id' => '0', 'name' => lang('ADMIN_MENU_ITEM_ADMIN_EITHER')],
                    ['id' => '1', 'name' => lang('ADMIN_MENU_ITEM_ADMIN_MUST')],
                ],
                'value' => 0, // default on create, loaded from data on modify
            ]),
            'config_key' => new FormField_Text([
                'display' => lang('ADMIN_MENU_CONFIG_KEY'),
                'hint_text' => lang('ADMIN_MENU_CONFIG_KEY_HINT'),
            ]),
            'private' => new FormField_Select([
                'display' => lang('ADMIN_MENU_ITEM_PRIVATE'),
                'hint_text' => lang('ADMIN_MENU_ITEM_PRIVATE_HINT'),
                'data' => [
                    [ 'id' => '0', 'name' => lang('PUBLIC') ],
                    [ 'id' => '1', 'name' => lang('PRIVATE') ],
                ]
            ]),
            'accessRow' => new Form_Row([
                'remove' => new Form_Panel([
                    'removeGroup' => new FormField_Table([
                        'th_row' => [
                            lang('MARK_TO_DELETE'),
                            lang('GROUP')
                        ],
                        'td_row' => [
                            '{CHECKBOX_ID}',
                            '<a href="admin_group.php?id={GROUP_ID}">{NAME}</a>',
                        ],
                        #'checkbox_label' => lang('MARK_TO_DELETE'),
                        'nodata' => '<p>'.lang('ADMIN_MENU_ITEM_NO_GROUP_ACCESS').'</p>',
                        'table_class' => 'table-condensed table-hover',
                        'isdbfield' => false,
                        'sql' => "SELECT groups.id, groups.name
                                    FROM $T[groups_menus] gm
                                    JOIN $T[groups] groups
                                        ON (gm.group_id = groups.id)
                                    WHERE gm.menu_id = ? ",
                        'bindvals' => [$menuId],
                    ], [
                        'button' => 'save',
                        'action' => 'delete',
                        'dbtable' => 'groups_menus',
                        'where' => [
                            'menu_id' => $menuId,
                            'group_id' => '{ID}',
                        ],
                    ]),
                ], [
                    'head' => '<h4>'.lang('ADMIN_MENU_DEL_GROUP_ACCESS').'</h4>',
                    'Panel_Class' => 'panel-default col-xs-12 col-sm-6',
                    'delete_if' => empty($menuId),
                ]),
                'add' => new Form_Panel([
                    'addGroup' => new FormField_Table([
                        'table_head_cells' => [
                            lang('MARK_TO_ADD'),
                            lang('GROUP'),
                        ],
                        'table_data_cells' => [
                            '{CHECKBOX_ID}{group_id(HIDDEN)}',
                            '<a href="admin_group.php?id={ID}">{NAME}</a>',
                        ],
                        #'checkbox_label' => lang('MARK_TO_ADD'),
                        'nodata' => '<p>'.lang('ADMIN_MENU_ITEM_NO_GROUP_WITHOUT_ACCESS').'</p>',
                        'table_class' => 'table-condensed table-hover',
                        'isdbfield' => false,
                        'sql' => $addSQL,
                        'bindvals' => [$menuId],
                    ], [
                        'button' => 'save',
                        'action' => 'insert',
                        'dbtable' => 'groups_menus',
                        'fields' => [
                            'menu_id' => $menuId,
                            'group_id' => '{ID}',
                        ],
                    ]),
                ], [
                    'head' => '<h4>'.lang('ADMIN_MENU_ADD_GROUP_ACCESS').'</h4>',
                    'Panel_Class' => 'panel-default col-xs-12 col-sm-6',
                    'delete_if' => empty($menuId),
                ]),
            ]),
        ], [
            'title' => lang('ADMIN_MENU_ITEM_ACCESS_TITLE'),
        ]),
    ]),
    'save' => new FormField_ButtonSubmit([
        'display' => lang('SAVE'),
    ]),
    'delete' => new FormField_ButtonDelete([
        'display' => lang('ADMIN_MENU_ITEM_DELETE'),
    ]),
], [
    'dbtable' => 'menus',
    'default' => 'process',
    'multi_delete_nothing' => false, // no message if nothing to do
    'multi_insert_nothing' => false, // no message if nothing to do
    # must set this explicitly to maintain the menu_title=$menuTitle arg
    'autoredirect' => 'admin_menu.php?menu_title='.$menuTitle,
]);
