<?php
/*
UserSpice
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

checkToken();

/*
 * admin_groups.php operates in 2 modes, both editing the `groups`
 * table. But some groups are normal groups ($mode == 'GROUP') and
 * some groups are group-role groups ($mode == 'ROLE'). Mostly the
 * only changes are labels and titles.
 *
 * If called directly as admin_groups.php then $mode is set to 'GROUP'
 * below. If called as admin_roles.php then $mode is set to 'ROLE'
 * and then admin_groups.php is included.
 */
if (isset($mode) && strtoupper($mode) == 'ROLE') {
    $mode = 'ROLE';
    $childForm = 'admin_role.php';
} else {
    $mode = 'GROUP';
    $childForm = 'admin_group.php';
}

# We need 2 separate <form>...</form> because of "required" fields
# in the "create" section which are not required if we are deleting
$myForm = new Form ([
    'deleteGroupForm' => new Form_Form ([
        '<h4>'.lang($mode.'_LIST_TITLE').'</h4>',
        'groupList' => new FormField_Table ([
            'field' => 'deleteGroups',
            'isdbfield' => false,
            'table_head_cells' => '<th>'.lang($mode.'_DELETE').'</th>'.
                '<th>'.lang($mode.'_NAME').'</th>'.
                '<th>'.lang($mode.'_SHORT_NAME').'</th>'.
                '<th>'.lang('GROUPTYPE_NAME').'</th>'.
                ($mode == 'ROLE' ? '' : '<th>'.lang('ROLE_LIST_TITLE').'</th>'),
            'table_data_cells' => '<td>{CHECKBOX_ID}</td>'.
                '<td><a href="'.$childForm.'?id={ID}">{NAME}</a></td>'.
                '<td>{SHORT_NAME}</td>'.
                '<td><a href="admin_grouptypes.php?id={grouptype_id}">{GROUPTYPE_NAME}</a></td>'.
                ($mode == 'ROLE' ? '' : '<td>{ROLES}</td>'  ),
            'nodata' => '<p>'.lang('NO_ROLES_ASSIGNED').'</p>',
            'Table_Class' => 'table table-bordered table-condensed',
            'Checkbox_Label' => lang('MARK_TO_DELETE'),
        ], [
            'debug' => 5,
            'Form_Name' => 'deleteGroupForm',
        ]),
        'save' => new FormField_ButtonDelete ([
            'display' => lang('DELETE_SELECTED_'.$mode.'S'),
        ]),
        'create' => new FormField_ButtonAnchor ([
            'display' => lang('CREATE_'.$mode),
            'link' => $childForm,
        ])
    ], ['Form_Name' => 'deleteForm']),
], [
    'title' => lang('ADMIN_'.$mode.'S_TITLE'), // ADMIN_GROUPS_TITLE or ADMIN_ROLES_TITLE
    'exclude_elements' => ['openForm', 'closeForm'],
    'dbtable' => 'groups',
    'Keep_AdminDashBoard' => true,
]);

//Forms posted
if(Input::exists('post')) {
    //Delete groups
    if($deletions = Input::get('deleteGroups', 'post')) {
        if ($deletion_count = deleteGroups($deletions, $errors)) {
            $successes[] = lang("GROUP_DELETIONS_SUCCESSFUL", array($deletion_count));
        }
    }
}

//List each group where is_role==n  n=0:(normal groups)/n=1:(role group)
$groups = fetchAllGroups(); //Retrieve list of all groups
$roleVal = (($mode == 'ROLE') ? 1 : 0);
$groupData = [];
foreach ($groups as $gd) {
    if ($gd->is_role != $roleVal) {
        continue;
    }
    $g = (array)$gd;
    $r = '';
    if ($mode != 'ROLE') {
        $sep = '';
        foreach (fetchRolesByGroup($gd->id) as $role) {
            $r .= $sep.'<span style="font-weight: bold">'.$role->short_name.'</span>: '.$role->fname.' '.$role->lname.' ('.$role->username.')';
            $sep = ', ';
        }
    }
    $g['roles'] = $r;
    $groupData[] = $g;
}
$myForm->getField('groupList')->setRepData($groupData);

echo $myForm->getHTML(['errors'=>$errors, 'successes'=>$successes]);
