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
/*
 * FORM: admin_group.php
 *  - always called with ?id=n to identify the row to view/modify
 *
 * This script makes possible:
 * - modifying/deleting existing rows in table `groups`
 * - adding/deleting rows in table `groups_users_raw`
 * - adding/deleting rows in table `groups_pages`
 *
 * "Roles" are a special kind of "Group" and are accessed via a special
 * $mode=='ROLE' mode of operation. If there is no $mode=='ROLE' then
 * "normal groups" are acted upon; if there is a $mode=='ROLE' then the
 * special "role groups" are acted upon. Typically $mode='ROLE' is executed
 * from admin_role.php which then includes this file.
 *
 * If you are coming from UserSpice4 or before then you are used to calling
 * "groups" by their old name, "permissions". All references, table names,
 * column names have been changed from "permissions" to "groups".
 *
 * Groups are very similar to user-groups in many operating systems. Authorizations
 * are granted to groups and all members of that group are granted that authorization.
 * Groups can be members of other groups (nested to 4 levels deep) and all members
 * of a child group are considered to be members of the parent group.
 *
 * For a description of the difference between "normal groups" and "role groups"
 * please see the comments at the top of us_core/forms/admin_role.php
 *
 * This script (that you are viewing) is included by users/forms/master_form.php.
 * All securePage() calls and other includes are executed prior to this script being
 * included. Thus in this script we just handle the work of the form itself.
 *
 * DO NOT CHANGE THIS SCRIPT. If you wish to customize it, COPY IT TO users/local/forms
 * and then modify it. users/forms/master_form.php will automatically detect your customized
 * version and load that copy rather than this one.
 */

checkToken();

if (isset($mode) && $mode == 'ROLE') {
    $modeName = 'Group Role';
    $parentScript = 'admin_roles.php';
} else {
    $modeName = 'Group';
    $mode = 'GROUP';
    $parentScript = 'admin_groups.php';
}

if (!($group_id = Input::get('id')) || !groupIdExists($group_id)) {
    $group_id = null;
    $creating = true;
} else {
    $creating = false;
}

$groupDetails = fetchGroupDetails($group_id);
if ($m = Input::get('msg')) {
    if ($groupDetails && in_array($m, ['GROUP_ADD_SUCCESSFUL'])) {
        $successes[] = lang($m, $groupDetails->name);
    }
}
if ($groupDetails) {
    $grouptype_id = $groupDetails->grouptype_id;
} else {
    $grouptype_id = null;
}
$grouptypesData = $db->queryAll('grouptypes')->results();
$myForm = new Form([
    'toc' => new FormField_TabToc(['field' => 'toc', ]),
    'tabs' => new FormTab_Contents ([
        'groupInfo' => new FormTab_Pane ([
            'name' => new FormField_Text ([
                'dbfield' => 'groups.name',
                'new_valid' => [
                    'action' => ($creating ? 'add' : 'update'),
                    'update_id' => $group_id,
                ],
            ]),
            'short_name' => new FormField_Text ([
                'dbfield' => 'groups.short_name',
                'new_valid' => [
                    'action' => ($creating ? 'add' : 'update'),
                    'update_id' => $group_id,
                ],
            ]),
            'grouptype_id' => new FormField_Select([
                'dbfield' => 'groups.grouptype_id',
                'repeat' => $grouptypesData,
                'display' => lang($mode.'_GROUPTYPE_LABEL'),
                'placeholder_row' => ['id'=>null, 'name'=>lang('CHOOSE_FROM_LIST_BELOW')],
                'new_valid' => [], // in case they make it required
                'keep_if' => (boolean)$grouptypesData,
            ]),
            'admin' => new FormField_Select([
                'field' => 'admin',
                'repeat' => [
                    ['id'=>0, 'name'=>lang('GROUP_NOT_ADMIN')],
                    ['id'=>1, 'name'=>lang('GROUP_MEMBERS_ARE_ADMIN')],
                ],
                'display' => lang('GROUP_IS_ADMIN'),
            ]),
            'deleteRolesWell' => new Form_Well([
                'deleteRoles' => new FormField_Table ([
                    'field' => 'deleteRoles',
                    'table_head_cells' => '<th>'.lang('DELETE_SELECTED').'</th><th>'.lang('GROUP_ROLE_NAME_USER').'</th>',
                    'table_data_cells' => '<td>{CHECKBOX}</td><td>{NAME} ({SHORT_NAME}): {FNAME} {LNAME} ({USERNAME})</td>',
                    'nodata' => '<p>'.lang('NO_ROLES_ASSIGNED').'</p>',
                ]),
            ], [
                'Well_Class'=>'well-sm',
                #'debug' => 1,
                'title'=>lang('DELETE_ROLE_ASSIGNMENTS'),
                'delete_if' => ($mode == 'ROLE'),
                'delete_if_empty' => true,
            ]),
            'newRoleWell' => new Form_Well([
                'newRole' => new FormField_Select([
                    'field' => 'newRole',
                    'display' => lang('CHOOSE_ROLE'),
                    # repeating data set below
                    'placeholder_row' => ['id'=>null, 'name'=>lang('CHOOSE_ROLE')],
                    'nodata' => '<p>'.lang('NO_ROLES_SETUP_GROUPTYPE').'</p>',
                    'isdbfield' => false,
                    'Hint_Text' => lang('GROUP_NEED_ROLE_AND_USER'),
                ]),
                'newRoleUser' => new FormField_Select([
                    'field' => 'newRoleUser',
                    'display' => lang('CHOOSE_GROUP_MEMBER'),
                    # repeating data set below
                    'idfield' => 'user_id',
                    'placeholder_row' => ['user_id'=>null, 'name'=>lang('CHOOSE_GROUP_MEMBER')],
                    'nodata' => '<p>'.lang('NO_MEMBERS_OF_GROUP').'</p>',
                    'isdbfield' => false,
                    'Hint_Text' => lang('GROUP_NEED_ROLE_AND_USER'),
                ]),
            ], [
                'Well_Class' => 'well-sm',
                'title' => lang('ASSIGN_NEW_ROLE_TO_GROUP'),
                'delete_if' => ($mode == 'ROLE'),
                'delete_if_empty' => true,
            ]),
        ], [
            'active_tab'=>'active',
            'tab_id'=>'groupInfo',
            'title'=>lang($mode.'_INFORMATION_TITLE'),
        ]),
        'groupMembers'=> new FormTab_Pane ([
            '<h2>'.lang('GROUP_MEMBERSHIP').'</h2>',
            new Form_Col ([
                '<h3>'.lang('SELECT_MEMBERS_TO_DELETE').'</h3>',
                '<h4>'.lang($mode.'_SELECT_USER_DEL_MEMBERS').'</h4>',
                'removeGroupUsers' => new FormField_Table ([
                    'field' => 'removeGroupUsers',
                    'Table_Class'=>'table-condensed',
                    'isdbfield' => false,
                    #'debug' => 0,
                    'table_head_cells' => '<th>'.
                        lang('SELECT_USER_BELOW').'</th><th>'.
                        lang('NAME_USERS_MEMBERS').'</th>',
                    'table_data_cells' => '<td>{CHECKBOX}</td><td>{NAME}</td>',
                    'nodata' => '<p>'.lang('NO_USER_MEMBERS_OF_GROUP').'</p>',
                    # repeating data set below
                ]),
                '<h4>'.lang('SELECT_GROUP_DEL_MEMBERS').'</h4>',
                'removeGroupGroups' => new FormField_Table ([
                    'field' => 'removeGroupGroups',
                    'Table_Class'=>'table-condensed',
                    'isdbfield' => false,
                    #'debug' => 0,
                    'table_head_cells' => '<th>'.
                        lang('SELECT_GROUP_BELOW').'</th><th>'.
                        lang('NAME_GROUPS_MEMBERS').'</th>',
                    'table_data_cells' => '<td>{CHECKBOX}</td><td>{NAME}</td>',
                    'nodata' => '<p>'.lang('NO_GROUP_MEMBERS_OF_GROUP').'</p>',
                    'delete_if' => ($mode == 'ROLE'),
                    # repeating data set below
                ]),
            ], ['Col_Class'=>'col-xs-12 col-sm-6']),
            new Form_Col ([
                '<h3>'.lang('SELECT_NONMEMBERS_TO_ADD').'</h3>',
                '<h4>'.lang('SELECT_USER_ADD_MEMBERS').'</h4>',
                'addGroupUsers' => new FormField_Table ([
                    'field' => 'addGroupUsers',
                    'Table_Class'=>'table-condensed',
                    'isdbfield' => false,
                    #'debug' => 0,
                    'table_head_cells' => '<th>'.
                        lang('SELECT_USER_BELOW').'</th><th>'.
                        lang('NAME_USERS_NON_MEMBERS').'</th>',
                    'table_data_cells' => '<td>{CHECKBOX}</td><td>{NAME}</td>',
                    'nodata' => '<p>'.lang('NO_USERS_TO_ADD').'</p>',
                    'delete_if' => ($mode == 'ROLE'),
                    # repeating data will be set below
                ]),
                '<h4>'.lang('SELECT_GROUP_ADD_MEMBERS').'</h4>',
                'addGroupGroups' => new FormField_Table ([
                    'field' => 'addGroupGroups',
                    'Table_Class'=>'table-condensed',
                    'isdbfield' => false,
                    #'debug' => 0,
                    'table_head_cells' => '<th>'.
                        lang('SELECT_GROUP_BELOW').'</th><th>'.
                        lang('NAME_GROUPS_NON_MEMBERS').'</th>',
                    'table_data_cells' => '<td>{CHECKBOX}</td><td>{NAME}</td>',
                    'nodata' => '<p>'.lang('NO_GROUPS_TO_ADD').'</p>',
                    'delete_if' => ($mode == 'ROLE'),
                    # repeating data will be set below
                ]),
            ], ['Col_Class'=>'col-xs-12 col-sm-6']),
        ], [
            'tab_id'=>'groupMembers',
            'title'=>lang('GROUP_MEMBERSHIP')
        ]),
        'groupAccess' => new FormTab_Pane ([
            '<h2>'.lang('GROUP_ACCESS_PAGES').'</h2>',
            new Form_Row ([
                new Form_Col ([
                    '<h3>'.lang('REMOVE_PAGE_ACCESS_GROUP').'</h3>',
                    'removePage' => new FormField_Table ([
                        'field' => 'removePage',
                        'Table_Class'=>'table-sm',
                        'isdbfield' => false,
                        #'debug' => 0,
                        'table_head_cells' => '<th>'.
                            lang('SELECT_PAGE_BELOW').'</th><th>'.
                            lang('NAME_ACCESSIBLE_PAGES').'</th>',
                        'table_data_cells' => '<td>{CHECKBOX}</td><td>{PAGE}</td>',
                        'nodata' => '<p>'.lang('GROUP_NO_ACCESS_PAGES').'</p>',
                        # repeating data will be set below
                    ]),
                ],  ['Col_Class'=>'col-xs-12 col-sm-6 col-md-4']),
                new Form_Col ([
                    '<h3>'.lang('ADD_PAGE_ACCESS_GROUP').'</h3>',
                    'addPage' => new FormField_Table ([
                        'field' => 'addPage',
                        'table_class' => 'table-sm',
                        'isdbfield' => false,
                        #'debug' => 0,
                        'table_head_cells' => '<th>'.
                            lang('SELECT_PAGE_BELOW').'</th><th>'.
                            lang('NAME_INACCESSIBLE_PAGES').'</th>',
                        'table_data_cells' => '<td>{CHECKBOX}</td><td>{PAGE}</td>',
                        'nodata' => '<p>'.lang('GROUP_NO_PAGES_TO_ADD').'</p>',
                        # repeating data will be set below
                    ]),
                ],  ['Col_Class'=>'col-xs-12 col-sm-6 col-md-4']),
                new Form_Col ([
                    '<h3>'.lang('PUBLIC_PAGES').'</h3>',
                    'publicPages' => new FormField_Table ([
                        'field' => 'publicPages',
                        'Table_Class' => 'table-sm',
                        'isdbfield' => false,
                        #'debug' => 0,
                        'table_head_cells' => '<th>'.
                            lang('PUBLIC_PAGES').'</th>',
                        'table_data_cells' => '<td>{PAGE}</td>',
                        'nodata' => '<p>'.lang('GROUP_NO_PUBLIC_PAGES').'</p>',
                        # repeating data will be set below
                    ]),
                ],  ['Col_Class'=>'col-xs-12 col-sm-6 col-md-4']),
            ]),
        ], [
            'tab_id' => 'groupAccess',
            'title' => lang('GROUP_ACCESS_TITLE'),
        ]),
    ]),
    'save' => new FormField_ButtonSubmit([
            'field' => 'save',
            'display' => lang($mode.'_SAVE')
        ]),
    'save_and_new' => new FormField_ButtonSubmit([
            'field' => 'save_and_new',
            'display' => lang($mode.'_SAVE_AND_NEW')
        ]),
    'save_and_return' => new FormField_ButtonSubmit([
            'field' => 'save_and_return',
            'display' => lang($mode.'_SAVE_AND_RETURN')
        ]),
    'deleteGroup' => new FormField_ButtonDelete([
            'field' => 'deleteGroup',
            'display' => lang($mode.'_DELETE'),
            'value' => $group_id,
        ]),
], [
    'table' => 'groups',
    'title' => ($creating ?
                    // CREATING_GROUP_TITLE -or- CREATING_ROLE_TITLE
                    lang('CREATING_'.$mode.'_TITLE') :
                    // ADMIN_GROUP_TITLE -or- ADMIN_ROLE_TITLE
                    lang('ADMIN_'.$mode.'_TITLE', $groupDetails->name)),
    'form_action' => 'admin_group.php?id='.$group_id,
    #'debug' => 5,
]);
$myForm->getField('toc')->setRepData(
    $myForm->getAllFields([], ['class'=>'FormTab_Pane', 'not_only_fields'=>true])
);

// Update data in the database for any changes on the form
$need_reload = false;
if (Input::exists('post')) {
    $myForm->setFieldValues($db->queryById('groups', $group_id)->first());
    $myForm->setNewValues($_POST);
    //Delete selected group
    if ($deletions = Input::get('deleteGroup', 'post')) {
        if ($deletion_count = deleteGroups($deletions, $errors)) {
            $successes[] = lang('GROUP_DELETIONS_SUCCESSFUL', array($deletion_count));
            Redirect::to($parentScript);
        } else {
            if (!$errors) {
                $errors[] = lang('SQL_ERROR');
            }
        }
    } else {
        //Update group info columns
        if ($creating) {
            if ($group_id = $myForm->insertIfValid($errors)) {
                $need_reload = true;
                $reload_msg = 'GROUP_ADD_SUCCESSFUL';
                $successes[] = lang('GROUP_ADD_SUCCESSFUL', $myForm->getField('name')->getNewValue());
            }
        } else {
            if ($myForm->updateIfValid($group_id, $errors)) {
                $successes[] = lang('GROUP_UPDATE_SUCCESSFUL', $myForm->getField('name')->getNewValue());
            }
        }

        //Add new roles
        if (($role_id = Input::get('newRole')) && ($roleuser_id = Input::get('newRoleUser'))) {
            # Add this user to this role for this group
            addGroupsRolesUsers($group_id, $role_id, $roleuser_id);
            # Add this user to the role-group in groups_users_raw
            addGroupsUsers_raw($role_id, $roleuser_id);
            $successes[] = lang('GROUP_ROLE_ADD_SUCCESSFUL');
        } elseif (Input::get('newRole') || Input::get('newRoleUser')) {
            #dbg('newRole='.Input::get('newRole'));
            #dbg('newRoleUser='.Input::get('newRoleUser'));
            $errors[] = lang('GROUP_NEED_ROLE_AND_USER');
        }

        //Delete roles
        if ($deletes = Input::get('deleteRoles')) {
            # 2nd arg indicates that db integrity should be maintained
            # by deleting from groups_users_raw if needed
            if (deleteGroupsRolesUsers($deletes, true)) {
                $successes[] = lang('GROUP_ROLE_DELETE_SUCCESSFUL');
            } else {
                $errors[] = lang('GROUP_ROLE_DELETE_FAILED');
            }
        }

        //Remove user(s) from group (both tables)
        if ($remove = Input::get('removeGroupUsers', 'post')) {
            if ($deletion_count = deleteGroupsUsers($group_id, $remove)) {
                $successes[] = lang('GROUP_REMOVE_USERS', array($deletion_count));
            } else {
                $errors[] = lang('SQL_ERROR');
            }
        }

        //Remove nested group(s) from group
        if ($remove = Input::get('removeGroupGroups', 'post')) {
            if ($deletion_count = deleteGroupsUsers($group_id, $remove, 1)) {
                $successes[] = lang('GROUP_REMOVE_GROUPS', array($deletion_count));
            } else {
                $errors[] = lang('SQL_ERROR');
            }
        }

        //Add users to group
        if ($add = Input::get('addGroupUsers', 'post')) {
            if ($addition_count = addGroupsUsers_raw($group_id, $add)) {
                $successes[] = lang('GROUP_ADD_USERS', array($addition_count));
            } else {
                $errors[] = lang('SQL_ERROR');
            }
        }

        //Add nested groups to group
        if ($add = Input::get('addGroupGroups', 'post')) {
            if ($addition_count = addGroupsUsers_raw($group_id, $add, 1)) {
                $successes[] = lang('GROUP_ADD_GROUPS', array($addition_count));
            } else {
                $errors[] = lang('SQL_ERROR');
            }
        }

        //Remove pages from group
        if ($remove = Input::get('removePage', 'post')) {
            if ($deletion_count = deleteGroupsPages($remove, $group_id)) {
                $successes[] = lang('GROUP_REMOVE_PAGES', array($deletion_count));
            } else {
                $errors[] = lang('SQL_ERROR');
            }
        }

        //Add access to pages
        if ($add = Input::get('addPage', 'post')) {
            if ($addition_count = addGroupsPages($add, $group_id)) {
                $successes[] = lang('GROUP_ADD_PAGES', array($addition_count));
            } else {
                $errors[] = lang('SQL_ERROR');
            }
        }
    }
}
# If we just created this new then we need to get the ?group_id=n into the
# URL so that we have it preserved in a normal fashion
if ($need_reload) {
    Redirect::to(getPageLocation('admin_group.php'), "id=$group_id&msg=$reload_msg");
}
$myForm->setFieldValues($db->queryById('groups', $group_id)->first());
$groupDetails = fetchGroupDetails($group_id);
if ($fld = $myForm->getField('deleteRoles')) {
    $fld->setRepData(fetchRolesByGroup($group_id));
}

//Retrieve list of accessible pages
$groupPages = fetchPagesByGroup($group_id);

//Retrieve list of users with and without membership
$groupMembers_users = fetchGroupMembers_raw($group_id, false, true);
$myForm->getField('removeGroupUsers')->setRepData($groupMembers_users);
if ($fld = $myForm->getField('removeGroupGroups')) {
    $groupMembers_groups = fetchGroupMembers_raw($group_id, true, false);
    $fld->setRepData($groupMembers_groups);
}
if ($fld = $myForm->getField('addGroupUsers')) {
    $nonGroupMembers_users = fetchNonGroupMembers_raw($group_id, false, true);
    $fld->setRepData($nonGroupMembers_users);
}
if ($fld = $myForm->getField('addGroupGroups')) {
    $nonGroupMembers_groups = fetchNonGroupMembers_raw($group_id, true, false);
    $fld->setRepData($nonGroupMembers_groups);
}
$groupPages = fetchPagesByGroup($group_id);
$myForm->getField('removePage')->setRepData($groupPages);
$nonGroupPages = fetchPagesNotByGroup($group_id, true);
$myForm->getField('addPage')->setRepData($nonGroupPages);
$publicPages = fetchPublicPages();
$myForm->getField('publicPages')->setRepData($publicPages);
if ($fld = $myForm->getField('newRole')) {
    $groupRoleData = fetchRolesByType($grouptype_id, true);
    $fld->setRepData($groupRoleData);
}
if ($fld = $myForm->getField('newRoleUser')) {
    $groupUsers = fetchUsersByGroup($group_id);
    $fld->setRepData($groupUsers);
}

# Check one more time to see if any fields or form sections need
# to be deleted (delete_if_empty sort of thing now that repeating
# data is loaded)
$myForm->checkDeleteIfEmpty(true);

echo $myForm->getHTML(['errors'=>$errors, 'successes'=>$successes]);
