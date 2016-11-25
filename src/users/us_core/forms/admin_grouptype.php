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
 *  - always called with ?id=n to identify the row to view/modify
 *
 * This script makes possible:
 * - modifying existing rows in table `grouptypes`
 *
 * See comments at the top of admin_grouptypes.php for a description of GroupTypes.
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

# grouptypes.id must be specified via ?id=n
if (!$grouptype_id = Input::get('id')) {
    Redirect::to('admin_grouptypes.php');
}

# Initialize the form with form fields and HTML snippets
$myForm = new Form([
        'name' => new FormField_Text('grouptypes.name', [
                        'new_valid' => [
                                    'action'=>'update',
                                    'update_id'=>$grouptype_id,
                                ],
                    ]),
        'short_name' => new FormField_Text('grouptypes.short_name', [
                        'new_valid' => [
                                    'action'=>'update',
                                    'update_id'=>$grouptype_id,
                                ],
                    ]),
        'save' => new FormField_ButtonSubmit('save', [
                    'label' => lang('SAVE_GROUP_TYPE_LABEL')
                ]),
        'delete' => new FormField_ButtonDelete('delete', [
                    'label' => lang('DELETE_GROUP_TYPE_LABEL'),
                    'value' => $grouptype_id,
                ]),
    ],
    [
        'table'=>'grouptypes'
    ]);

#
# Update the database with any form data in $_POST
#
if (Input::exists('post')) {
    $myForm->setFieldValues($db->findById('grouptypes', $grouptype_id)->first());
    $myForm->setNewValues($_POST);
    if ($myForm->updateIfChangedAndValid($grouptype_id, $errors)) {
        $successes[] = lang('GROUPTYPE_UPDATE_SUCCESS', $myForm->getField('name')->getNewValue());
    }
    if ($deletes = Input::get('delete')) {
        deleteGrouptypes($deletes, $errors, $successes);
    }
}

#
# Prepare all data for displaying the form
#
$myForm->setFieldValues($db->findById('grouptypes', $grouptype_id)->first());

#
# Display the form
#
echo $myForm->getHTML(['header', 'admin', 'footers', 'errors'=>$errors, 'successes'=>$successes]);
