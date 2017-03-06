<?php

if (Input::exists()) {
    if ($creations = Input::get('createPages')) {
        $successes[] = lang('PAGES_ADD_SUCCESSFUL', createPages($creations));
    }
    if ($deletions = Input::get('deletePages')) {
        $successes[] = lang('PAGES_DELETE_SUCCESSFUL', deletePages($deletions));
    }
}

//Get list of php files for each $path
$paths=configGet('us_page_path', US_URL_ROOT);
$pages=[];
foreach ((array)$paths as $path){
    if (substr($path, -1, 1) != '/') {
        $path .= '/';
    }
    $pages = array_merge($pages, glob(US_DOC_ROOT.$path.'*.php'));
}
$pages = str_replace(US_DOC_ROOT, '', $pages);
$irrelevant = [];
foreach ($pages as $k=>$p) {
    if (in_array(basename($p), ['z_us_root.php', 'init.php'])) {
        $irrelevant[] = $k;
    }
}
foreach ($irrelevant as $k) {
    unset($pages[$k]);
}
sort($pages);

$dbpages = fetchAllPages(); //Retrieve list of pages in pages table
foreach ($dbpages as $p) {
    $dbp[$p->id] = $p->page;
}
$creations = array_diff($pages, $dbp);
$deletions = array_diff($dbp, $pages);

//Update $dbpages for display in the form
$dbpages = fetchAllPages();
foreach ($dbpages as &$p) {
    if ($p->private) {
        $p->access = lang('PRIVATE');
    } else {
        $p->access = lang('PUBLIC');
    }
}
//Update $deletions for display in the Form
$deleteList = [];
foreach ($deletions as $k=>$v) {
    if ($db->query("SELECT id FROM $T[menus] WHERE page_id = ? ",[$k])->count() > 0) {
        $inMenu = '<strong>*</strong>';
    } else {
        $inMenu = '&nbsp;';
    }
    $deleteList[] = array('id'=>$k, 'page'=>$v, 'in_menu'=>$inMenu);
}
//Update $creations for display in the Form
$createList = [];
foreach ($creations as $k=>$v) {
    $createList[] = array('id'=>$k, 'page'=>$v, 'value'=>$v);
}

$childForm = 'admin_page.php';
$myForm = new Form ([
    'addPages' => new Form_Panel([
        'createPages' => new FormField_Table ([
            'field' => 'createPages',
            'table_head_cells' => '<th>'.lang('MARK_TO_CREATE').'</th>'.
                '<th>'.lang('PAGE').'</th>',
            'table_data_cells' => '<td>{CHECKBOX_VALUE}</td>'.
                '<td><a href="'.$childForm.'?id={ID}">{PAGE}</a></td>',
            'repeat' => $createList,
            'checkbox_label' => lang('MARK_TO_CREATE'),
            'nodata' => '<p>'.lang('NO_PAGES').'</p>',
            'table_class' => 'table table-sm table-condensed table-inverse table-hover',
            'searchable' => true,
        ]),
        'create' => new FormField_ButtonSubmit ([
            'display' => lang('CREATE_MARKED_PAGES'),
        ])
    ], [
        'head' => '<h4>'.lang('SCRIPTS_NOT_IN_DB').'</h4>',
        'delete_if_empty' => true,
    ]),
    'delPages' => new Form_Panel([
        'deletePages' => new FormField_Table ([
            'field' => 'deletePages',
            'table_head_cells' => '<th>'.lang('MARK_TO_DELETE').'</th>'.
                '<th>'.lang('PAGE').'</th>',
            'table_data_cells' => '<td>{CHECKBOX_ID}</td>'.
                '<td>{IN_MENU}&nbsp;<a href="'.$childForm.'?id={ID}">{PAGE}</a></td>',
            'repeat' => $deleteList,
            'checkbox_label' => lang('MARK_TO_DELETE'),
            'nodata' => '<p>'.lang('NO_PAGES').'</p>',
            'table_class' => 'table table-sm table-condensed table-inverse table-hover',
            'searchable' => true,
        ]),
        'delete' => new FormField_ButtonSubmit ([
            'display' => lang('DELETE_MARKED_PAGES'),
        ])
    ], [
        'head' => '<h4>'.lang('SCRIPTS_NOT_EXIST').'</h4>',
        'foot' => '<h4><strong>*</strong> '.lang('PAGES_IN_MENU').'</h4>',
        'delete_if_empty' => true,
    ]),
    'q' => new FormField_SearchQ (['field_name'=>'q']),
    '<br />',
    'pageList' => new FormField_Table ([
        'field' => 'pageList',
        'isdbfield' => false,
        'table_head_cells' => '<th>'.lang('PAGE').'</th>'.
            '<th>'.lang('ACCESS').'</th>',
        'table_data_cells' =>
            '<td><a href="'.$childForm.'?id={ID}">{PAGE}</a></td>'.
            '<td>{ACCESS}</td>',
        'repeat' => $dbpages,
        'nodata' => '<p>'.lang('NO_PAGES').'</p>',
        'searchable' => true,
    ]),
    'save' => new FormField_ButtonSubmit([
        'field' => 'save',
        'display' => lang('SAVE_CHANGES'),
    ])
], [
    'title' => lang('ADMIN_PAGES_TITLE'),
    'Keep_AdminDashBoard' => true,
]);

$myForm->checkDeleteIfEmpty();
echo $myForm->getHTML(['errors'=>$errors, 'successes'=>$successes]);
