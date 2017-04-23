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
 * FORM: admin_grouptype.php
 *  - called with ?id=n to view/modify/delete an existing row
 *  - called without ?id=n to add a new row
 *
 * This script makes possible:
 * - add/modify/delete a single row at a time in table `grouptypes`
 *
 * See comments at the top of admin_grouptypes.php for a description of GroupTypes.
 *
 * This script (that you are viewing) is included by users/forms/master_form.php.
 * All securePage() calls and other includes (init.php, header, navigation, footers)
 * are executed external to this script. Thus in this script we just handle the work
 * of the form itself.
 *
 * DO NOT CHANGE THIS SCRIPT. Don't change any files under users/core/. If you wish to
 * customize this script, COPY IT TO users/local/forms and then modify it.
 * users/forms/master_form.php will automatically detect your customized version and
 * load that copy rather than this one.
 */

checkToken();

# grouptypes.id is specified via ?id=n; if not we create a new row
if (!$grouptype_id = Input::get('id')) {
    $creating = true;
} else {
    $creating = false;
}

# Initialize the form with form fields and HTML snippets
$myForm = new Form([
    'name' =>
        new FormField_Text([
            'dbfield' => 'grouptypes.name', // see `field_defs`
            'new_valid' => [
                'action'=>($creating?'add':'update'),
                'update_id'=>$grouptype_id,
            ],
        ]),
    'short_name' =>
        new FormField_Text([
            'dbfield' => 'grouptypes.short_name', // see `field_defs`
            'new_valid' => [
                'action'=>($creating?'add':'update'),
                'update_id'=>$grouptype_id,
            ],
        ]),
    'save' =>
        new FormField_ButtonSubmit([
            'display' => lang('GROUPTYPE_SAVE')
        ]),
    'delete' =>
        new FormField_ButtonDelete([
            'display' => lang('GROUPTYPE_DELETE'),
            'value' => $grouptype_id,
            'delete_if' => $creating,
        ]),
], [
    'table' => 'grouptypes',
    'TableId' => $grouptype_id,
    'default' => 'autoprocess',
    'insert_success_message' => 'GROUPTYPE_ADD_SUCCESSFUL',
    'update_success_message' => 'GROUPTYPE_UPDATE_SUCCESSFUL',
    'insert_failed_message' => 'GROUPTYPE_ADD_FAILED',
    'update_failed_message' => 'GROUPTYPE_UPDATE_FAILED',
    'Keep_AdminDashBoard' => true,
]);
