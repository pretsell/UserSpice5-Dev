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
    $roleVal = 1;
} else {
    $mode = 'GROUP';
    $childForm = 'admin_group.php';
    $roleVal = 0;
}

class FormField_GroupTable extends FormField_Table {
    public $roleVal=null;
    public $mode=null;
    public function handle1Opt($name, &$val) {
        #dbg("handle1Opt(): name=$name, val=".print_r($val,true));
        switch (strtolower(str_replace(['_', '-'], '', $name))) {
            case 'roleval':
                $this->roleVal = $val;
                return true;
            case 'mode':
                $this->mode = $val;
                return true;
        }
        return parent::handle1Opt($name, $val);
    }
    public function setRepData($val) {
        $rtn = parent::setRepData($val);
        $groupData = [];
        #dbg("roleVal set to ".$this->roleVal);
        #dbg("setRepData(): getRepData() before");
        #var_dump($this->getRepData());
        foreach ($this->repData as &$g) {
            #dbg("BEFORE");
            #var_dump($g);
            if ($g['is_role'] != $this->roleVal) {
                continue;
            }
            #$g = (array)$gd;
            $r = '';
            if ($this->mode != 'ROLE') {
                $sep = '';
                foreach (fetchRolesByGroup($g['id']) as $role) {
                    $r .= $sep.'<span style="font-weight: bold">'.$role->short_name.'</span>: '.$role->fname.' '.$role->lname.' ('.$role->username.')';
                    $sep = ', ';
                }
            }
            $g['roles'] = $r;
            $g['foo'] = 'abc';
            $g['bar'] = 'xyz';
            #dbg("AFTER");
            #var_dump($g);
            $groupData[] = $g;
        }
        $this->repData = $groupData;
    }
}

#dbg("JUST BEFORE FULL INITIALIZATION OF MYFORM");
# We need 2 separate <form>...</form> because of "required" fields
# in the "create" section which are not required if we are deleting
$myForm = new Form ([
    'delGroups' => new FormField_GroupTable ([
        'isdbfield' => false,
        'table_head_cells' => '<th>'.lang($mode.'_DELETE').'</th>'.
            '<th>'.lang($mode.'_NAME').'</th>'.
            '<th>'.lang($mode.'_SHORT_NAME').'</th>'.
            '<th>'.lang('GROUPTYPE_NAME').'</th>'.
            ($mode == 'ROLE' ? '' : '<th>'.lang('ROLE_LIST_TITLE').'</th>'),
        'table_data_cells' => '<td>{CHECKBOX_ID}</td>'.
            '<td><a href="'.$childForm.'?id={ID}">{NAME}</a></td>'.
            '<td>{SHORT_NAME}</td>'.
            '<td><a href="admin_grouptype.php?id={grouptype_id}">{GROUPTYPE_NAME}</a></td>'.
            ($mode == 'ROLE' ? '' : '<td>{ROLES}</td>'  ),
        'nodata' => '<p>'.lang('NO_ROLES_ASSIGNED').'</p>',
        'Table_Class' => 'table table-bordered table-condensed',
        'Checkbox_Label' => lang('MARK_TO_DELETE'),
        'sql' => "SELECT groups.*, gt.name as grouptype_name FROM $T[groups] groups LEFT JOIN $T[grouptypes] gt ON (gt.id = groups.grouptype_id) WHERE groups.is_role = ? ORDER BY groups.name",
        #'sql' => "SELECT groups.id, groups.name, groups.short_name, groups.is_role, gt.name as grouptype_name FROM $T[groups] groups LEFT JOIN $T[grouptypes] gt ON (gt.id = groups.grouptype_id) WHERE groups.is_role = ? ORDER BY groups.name",
        'bindvals' => [$roleVal],
        'roleval' => $roleVal,
        'mode' => $mode,
    ], [
        'button' => 'deleteMulti',
        'where' => ['id' => '{delGroups}'],
    ]),
    'deleteMulti' => new FormField_ButtonDelete ([
        'display' => lang('DELETE_SELECTED_'.$mode.'S'),
    ]),
    'create' => new FormField_ButtonAnchor ([
        'display' => lang('CREATE_'.$mode),
        'link' => $childForm,
    ])
], [
    'title' => lang('ADMIN_'.$mode.'S_TITLE'), // ADMIN_GROUPS_TITLE or ADMIN_ROLES_TITLE
    #'exclude_elements' => ['openForm', 'closeForm'],
    'dbtable' => 'groups',
    'Keep_AdminDashBoard' => true,
    'default' => 'processing',
    'multirow' => true,
    #'debug'=>5,
]);
#dbg("AFTER FULL INITIALIZATION OF MYFORM");

/*
//Forms posted
if(Input::exists('post')) {
    //Delete groups
    if($deletions = Input::get('delGroups', 'post')) {
        if ($deletion_count = deleteGroups($deletions, $errors)) {
            $successes[] = lang("GROUP_DELETIONS_SUCCESSFUL", array($deletion_count));
        }
    }
}

//List each group where is_role==n  n==0:(normal groups)/n==1:(role group)
$groups = fetchAllGroups(); //Retrieve list of all groups
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
#$myForm->getField('delGroups')->setRepData($groupData);

#dbg("Just before getHTML()");
echo $myForm->getHTML(['errors'=>$errors, 'successes'=>$successes]);
*/
