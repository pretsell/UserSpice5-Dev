<?php
checkToken();
if (!$menuTitle=Input::get('menu_title')) {
    $errors[] = "ERROR: No menu title specified";
    Redirect::to('admin_menus.php');
}
$sql = "SELECT menus.*, pages.page,
            '' AS prefix, 0 AS nest_level,
            LPAD(display_order, 5, '0') AS sort_order
        FROM $T[menus] menus
        LEFT JOIN $T[pages] pages ON (menus.page_id = pages.id)
        WHERE menus.menu_title = ?
          AND parent = -1
        UNION
        SELECT menus.*, pages.page,
            '>>>&nbsp;' AS prefix, 1 AS nest_level,
            concat(LPAD(m2.display_order, 5, '0'), '-', LPAD(menus.display_order, 5, '0'))
        FROM $T[menus] menus
        LEFT JOIN $T[pages] pages ON (menus.page_id = pages.id)
        JOIN $T[menus] m2 ON (m2.id = menus.parent)
        WHERE menus.menu_title = ?
          AND m2.parent = -1
        UNION
        SELECT menus.*, pages.page,
            '>>>&nbsp;>>>&nbsp;' AS prefix, 2 AS nest_level,
            concat(LPAD(m3.display_order, 5, '0'), '-', LPAD(m2.display_order, 5, '0'), '-', LPAD(menus.display_order, 5, '0'))
        FROM $T[menus] menus
        LEFT JOIN $T[pages] pages ON (menus.page_id = pages.id)
        JOIN $T[menus] m2 ON (m2.id = menus.parent)
        JOIN $T[menus] m3 ON (m3.id = m2.parent)
        WHERE menus.menu_title = ?
          AND m3.parent = -1
        ORDER BY sort_order
        ";
$bindVals = [$menuTitle, $menuTitle, $menuTitle];

/*
 * Handle renumbering
 */
# Note that $menus is used both for renumbering (here) and for calculating $parentSelect below
$menus = $db->query($sql, $bindVals)->results(true);
$db->errorSetMessage($errors);
if (!$db->error() && Input::get('renumMenu')) {
    $numbers = [];
    $lastLevel = -1;
    foreach ($menus as $menu) {
        if ($menu['nest_level'] > $lastLevel) {
            $numbers[$menu['nest_level']] = 10;
            $lastLevel = $menu['nest_level'];
        } elseif ($menu['nest_level'] < $lastLevel) {
            for ($i = $menu['nest_level']+1; $i <= $lastLevel; $i++) {
                unset($numbers[$i]);
            }
            $numbers[$menu['nest_level']] += 10;
            $lastLevel = $menu['nest_level'];
        } else { // equal
            $numbers[$menu['nest_level']] += 10;
        }
        $fields = ['display_order' => $numbers[$menu['nest_level']]];
        $db->update('menus', $menu['id'], $fields);
    }
}

/*
 * Calculate $pageSelect
 */
$pages = $db->queryAll('pages', [], [], 'page')->results();
$pageSelect = ['' => lang('ADMIN_MENU_NO_PAGE_ASSIGNED')];
foreach ($pages as $page) {
    $pageSelect[$page->id] = basename($page->page);
}

/*
 * Calculate $parentSelect
 */
$parentSelect = ['-1' => lang('ADMIN_MENU_TOP_LEVEL')];
# Note that we are re-using $menus from renumbering (above) -- no need to re-query
foreach ($menus as &$menu) {
    if ($menu['nest_level'] <= 1) {
        $parentSelect[$menu['id']] = $menu['prefix'].lang($menu['label_token']);
    }
}
$myForm = new Form([
    'menuDisplay' => new FormField_HTML([
        'display' => 'Example Menu',
        'hint_text' => lang('ADMIN_MENU_GOES_NOWHERE'),
        #'value' => getMenu($menuTitle, null, true),
    ]),
    'menus' => new FormField_Table([
        'th_row' => [
            '{'.lang('MARK_TO_DELETE').'(checkallbox)}',
            lang('ADMIN_MENU_ICON_LABEL'),
            lang('ADMIN_MENU_INFO'),
            lang('ADMIN_MENU_PARENT'),
            lang('ADMIN_MENU_PAGE'),
            lang('ADMIN_MENU_LOGGED_IN'),
            lang('ADMIN_MENU_ADMIN'),
            lang('ADMIN_MENU_DISPLAY_ORDER'),
        ],
        'td_row' => [
            '{menus2Delete(CHECKBOX)}',
            '{HIDDEN_ID} {prefix}<span class="{icon_class}"></span><a href="admin_menu_item.php?id={ID}">{label_val}</a>',
            '<div class="fa fa-info-circle" data-container="body" data-html="true" data-placement="top" data-toggle="tooltip" title="{HINT}"></div>',
            '{parent(SELECT)}',
            '{page_id(SELECT)}',
            '{logged_in(SELECT)}',
            '{admin(SELECT)}',
            '{display_order(TEXT)}',
        ],
        'sql' => $sql,
        'bindvals' => $bindVals,
        'postfunc' => 'fixMenu',
        'select(parent)' => $parentSelect,
        'select(page_id)' => $pageSelect,
        'select(logged_in)' => [
            0 => lang('ADMIN_MENU_LOGGED_IN_EITHER'),
            -1 => lang('ADMIN_MENU_LOGGED_IN_CANNOT'),
            1 => lang('ADMIN_MENU_LOGGED_IN_MUST'),
        ],
        'select(admin)' => [
            0 => lang('ADMIN_MENU_ADMIN_EITHER'),
            -1 => lang('ADMIN_MENU_ADMIN_CANNOT'),
            1 => lang('ADMIN_MENU_ADMIN_MUST'),
        ],
        'Script' => '<script>$(function () { $(\'[data-toggle="tooltip"]\').tooltip() });</script>',
    ], [
        'action' => 'update',
        'button' => 'updateMenus',
        'fields' => [
            'parent' => '{parent}',
            'page_id' => '{page_id}',
            'logged_in' => '{logged_in}',
            'admin' => '{admin}',
            'display_order' => '{display_order}',
        ],
    ]),
    'updateMenus' => new FormField_ButtonSubmit([
        'display' => lang('SAVE_CHANGES'),
    ]),
    'newItem' => new FormField_ButtonAnchor([
        'display' => lang('ADMIN_MENU_NEW_ITEM'),
        'href' => 'admin_menu_item.php?menu_title='.$menuTitle,
    ]),
    'deleteItems' => new FormField_ButtonSubmit([
        'display' => lang('ADMIN_MENU_DELETE_ITEMS'),
    ]),
    'renumMenu' => new FormField_ButtonSubmit([
        'display' => lang('ADMIN_MENU_RENUMBER'),
    ]),
], [
    'dbtable' => $T['menus'],
    'default' => 'process',
    'autoshow' => false, // we need to load the example menu after processing but before displaying
    'multirow' => true, // redirect as a multi-row form
    'multiDelete' => [
        'idfield' => 'menus2Delete',
        'action' => 'delete',
        'button' => 'deleteItems',
        'idbyidx' => true,
    ],
    # Make the tooltip wider by custom CSS in the header
    'headercode' => '<style>.tooltip-inner { max-width:350px !important; }</style>',
]);

$myForm->getField('menuDisplay')->setFieldValue(getMenu($menuTitle, null, true));

echo $myForm->getHTML();

/*
 * fixMenu() - this function is called via 'postfunc' in the form above
 * and fills out a couple calculated fields in $menus
 *
 * Doing it all within the form allows 'default' => 'process' to handle
 * all the reloading after changes and etc.
 */
function fixMenu(&$menus, $args=[]) {
    foreach ($menus as &$menu) {
        $menu['label_val'] = lang($menu['label_token']);
        $menu['hint'] = ($menu['page']?$menu['page'].' (page)':$menu['link'].' (link)').'<br />Icon Class: '.$menu['icon_class'].'<br />Language Token: '.$menu['label_token'];
    }
}
