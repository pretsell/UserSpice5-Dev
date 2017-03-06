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
 * admin_grouptypes.PHP
 *
 * This script makes possible:
 * - viewing/listing of rows in table `grouptypes`
 * - deleting rows in table `grouptypes`
 * - creation of rows in table `grouptypes`
 * - modifying existing rows in table `grouptypes` (via links to admin_grouptype.php)
 *
 * GroupTypes are linked from groups.grouptype_id and provide a mapping between
 * normal groups and "role groups".
 *
 * For instance, you may have "team" and "department" GroupTypes.
 * "team" groups might allow these roles: "team leader", "QA Lead",
 * "systems analyst" and "business analyst".
 * "department" groups might have these roles: "manager" and
 * "HR Representative".
 * By setting grouptypes you allow only those roles designated for those
 * types of groups to be assigned for those groups. In other words, when
 * editing "department" groups you will not even see the "team leader" role
 * because the grouptype doesn't match.
 *
 * This script (that you are viewing) is included by users/forms/master_form.php.
 * All securePage() calls and other includes are executed prior to this script being
 * included. Thus in this script we just handle the work of the form itself.
 *
 * DO NOT CHANGE THIS SCRIPT. If you wish to customize it, COPY IT TO users/local/forms
 * and then modify it. users/forms/master_form.php will automatically detect your customized
 * version and load that copy rather than this one.
 */

# Check the CSRF token
checkToken();

# Where to go to edit/delete individual grouptype rows
$childForm = getPageLocation('admin_grouptype.php');

# Initialize the form with form fields and HTML snippets
$myForm = new Form([
    'grouptype_list' =>
        new FormField_Table([
            'field' => 'grouptype_list',
            'isdbfield' => false,
            // from other side
            'table_head_cells' => '<th>'.lang('GROUPTYPE_MARK_TO_DELETE').'</th>'.
                '<th>'.lang('GROUPTYPE_NAME_LABEL').'</th>'.
                '<th>'.lang('GROUPTYPE_SHORT_NAME_LABEL').'</th>',
            'table_data_cells' => '<td>{CHECKBOX_ID}</td>'.
                '<td><a href="'.$childForm.'?id={ID}">{NAME}</a></td>'.
                '<td>{SHORT_NAME}</td>',
                #'<td><a href="admin_grouptypes.php?id={grouptype_id}">{GROUPTYPE_NAME}</a></td>'.
            'nodata' => '<p>'.lang('GROUPTYPES_DO_NOT_EXIST').'</p>',
            'Table_Class' => 'table table-bordered table-condensed',
            'Checkbox_Label' => lang('MARK_TO_DELETE'),
        ]),
    'delete' =>
        new FormField_ButtonDelete([
            'field' => 'delete',
            'display' => lang('GROUPTYPE_DELETE_MARKED'),
        ]),
    'create' =>
        new FormField_ButtonAnchor([
            'field' => 'create',
            'display' => lang('GROUPTYPE_CREATE'),
            'link' => $childForm,
        ]),
], [
    'title' => lang('ADMIN_GROUPTYPES_TITLE'),
    'Keep_AdminDashBoard' => true,
]);

#
# Update the database with any form data in $_POST
#
if (Input::exists('post')) {
    if (@$_REQUEST['create'] || @$_POST['name']) {
        $myForm->setNewValues($_POST);
        if ($myForm->checkFieldValidation()) {
            $fields = $myForm->fieldListNewValues();
            if ($db->insert('grouptypes', $fields)) {
                $successes[] = lang('GROUPTYPE_ADD_SUCCESSFUL', $myForm->getField('name')->getNewValue());
            } else {
                $errors[] = lang(SQL_ERROR);
            }
        } else {
            $errors = $validation->stackErrorMessages($errors);
        }
    }
    if ($deletes = Input::get('grouptype_list')) {
        deleteGrouptypes($deletes, $errors, $successes);
    }
}

#
# Prepare all data for displaying the form
#
$myForm->getField('grouptype_list')->setRepData($db->queryAll('grouptypes', [],  'name')->results());

#
# Display the form
#
echo $myForm->getHTML(['errors'=>$errors, 'successes'=>$successes]);
