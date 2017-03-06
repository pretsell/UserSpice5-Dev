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
 * FORM: admin_users.php
 *
 * This script makes possible:
 * - deleting existing rows in table `users`
 * - identifying and editing (via admin_user.php) rows in table `users`
 * - adding (via admin_user_add.php) rows in table `users`
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

$myForm = new Form ([
    'q' => new FormField_SearchQ (['field_name'=>'q']),
    'delete' => new FormField_Table ([
        'field' => 'delete',
        'isdbfield' => false,
        'table_head_cells' => '<th>'.lang('DELETE').'</th>'.
            '<th>'.lang('USERNAME').'</th>'.
            '<th>'.lang('EMAIL').'</th>'.
            '<th>'.lang('FNAME').'</th>'.
            '<th>'.lang('LNAME').'</th>'.
            '<th>'.lang('JOIN_DATE').'</th>'.
            '<th>'.lang('LAST_SIGN_IN').'</th>'.
            '<th>'.lang('LOGINS').'</th>',
        'table_data_cells' => '<td>{CHECKBOX_ID}</td>'.
            '<td><a href="admin_user.php?id={ID}">{USERNAME}</a></td>'.
            '<td>{EMAIL}</td>'.
            '<td>{FNAME}</td>'.
            '<td>{LNAME}</td>'.
            '<td>{JOIN_DATE}</td>'.
            '<td>{LAST_LOGIN}</td>'.
            '<td>{LOGINS}</td>',
        'nodata' => '<p>'.lang('NO_USERS_DEFINED').'</p>',
		'sql_cols' => "*",
		'sql_from' => "$T[users] users",
		'sql_where'=> "1",
        'sql_order'=> "fname, lname, id",
        #'sql_bindvals'=> [$group_id],
        'pageItems' => 5,
        'pageVarName' => 'uPage',
        'Checkbox_Label' => lang('MARK_TO_DELETE'),
        'searchable' => true,
    ], [

    ]),
    'deleteUsers' => new FormField_ButtonDelete ([
        'display' => lang('DELETE_SELECTED_USERS'),
    ]),
    'addUsers' => new FormField_ButtonAnchor ([
        'display' => lang('CREATE_USER'),
        'href' => 'admin_user.php',
    ]),
], [
    'Keep_AdminDashBoard' => true,
]);

//Forms posted
if (!empty($_POST)) {
  //Delete User Checkboxes
  if (!empty($_POST['deleteUsers']) && !empty($_POST['delete'])) {
    $deletions = Input::get('delete');
    if ($deletion_count = deleteUsers($deletions)) {
      $successes[] = "Account deletion successful";
    }
    else {
      $errors[] = "SQL Error";
    }
  }
}

echo $myForm->getHTML(['errors'=>$errors, 'successes'=>$successes]);
