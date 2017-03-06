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
    $creating = true;
    #Redirect::to('admin_grouptypes.php');
} else {
    $creating = false;
}
$parentPage = getPageLocation('admin_grouptypes.php');
$currentPage = getPageLocation('admin_grouptype.php');

# Initialize the form with form fields and HTML snippets
$myForm = new Form([
    'toc' => new FormField_TabToc(['TocType'=>'tab']),
    'tab' => new FormTab_Contents([
        'tab1' => new FormTab_Pane([
            'name' =>
                new FormField_Text([
                    'dbfield' => 'grouptypes.name',
                    'new_valid' => [
                                'action'=>($creating?'add':'update'),
                                'update_id'=>$grouptype_id,
                            ],
                ]),
        ], ['active_tab'=>'active', 'tab_id'=>'tab1', 'title'=>"Hello"]),
        'tab2' => new FormTab_Pane([
            'short_name' =>
                new FormField_Text([
                    'dbfield' => 'grouptypes.short_name',
                    'new_valid' => [
                                'action'=>($creating?'add':'update'),
                                'update_id'=>$grouptype_id,
                            ],
                ]),
        ], [ 'tab_id'=>'tab2', 'title'=>"Good-bye" ]),
    ]),
    'save' => new FormField_ButtonSubmit([
                'field' => 'save',
                'display' => lang('GROUPTYPE_SAVE')
            ]),
    'save_and_new' => new FormField_ButtonSubmit([
                'field' => 'save_and_new',
                'display' => lang('GROUPTYPE_SAVE_AND_NEW')
            ]),
    'save_and_return' => new FormField_ButtonSubmit([
                'field' => 'save_and_return',
                'display' => lang('GROUPTYPE_SAVE_AND_RETURN')
            ]),
    'delete' => new FormField_ButtonDelete([
                'field' => 'delete',
                'display' => lang('GROUPTYPE_DELETE'),
                'value' => $grouptype_id,
                'delete_if' => $creating,
            ]),
], [
    'title' => lang('ADMIN_GROUPTYPE_TITLE'),
    'table' => 'grouptypes',
    'Keep_AdminDashBoard' => true,
]);

# Fill in the ToC dynamically to ensure we have the appropriate labels
$myForm->getField('toc')->setRepData($myForm->getAllFields([], ['class'=>'FormTab_Pane', 'not_only_fields'=>true]));

#
# Update the database with any form data in $_POST
#
if (Input::exists('post')) {
    $need_reload = false;
    if ($deletes = Input::get('delete')) {
        deleteGrouptypes($deletes, $errors, $successes);
        Redirect::to($parentPage);
    } else {
        $myForm->setNewValues($_POST);
        if ($creating) {
            if ($newid = $myForm->insertIfValid($errors)) {
                $successes[] = lang('GROUPTYPE_ADD_SUCCESSFUL', $myForm->getField('name')->getNewValue());
                $need_reload = true;
            }
        } else {
            $myForm->setFieldValues($db->queryById('grouptypes', $grouptype_id)->first());
            if ($myForm->updateIfValid($grouptype_id, $errors)) {
                $successes[] = lang('GROUPTYPE_UPDATE_SUCCESSFUL', $myForm->getField('name')->getNewValue());
                if (!Input::get('save')) {
                    $need_reload = true;
                }
            }
        }
    }
    if ($need_reload) {
        if (Input::get('save')) {
            Redirect::to($currentPage, "id=$newid&msg=GROUPTYPE_ADD_SUCCESSFUL");
        } elseif (Input::get('save_and_new')) {
            Redirect::to($currentPage, "last_id=$newid&msg=GROUPTYPE_ADD_SUCCESSFUL");
        } elseif (Input::get('save_and_return')) {
            Redirect::to($parentPage, "last_id=$newid&msg=GROUPTYPE_ADD_SUCCESSFUL");
        }
    }
}

#
# Prepare all data for displaying the form
#
$grouptypeDetails = $db->queryById('grouptypes', $grouptype_id)->first();
$myForm->setFieldValues($grouptypeDetails);

if ($m = Input::get('msg')) {
    if ($last_id = Input::get('last_id')) {
        $grouptypeDetails = $db->queryById('grouptypes', $last_id)->first();
    }
    if ($grouptypeDetails && in_array($m, ['GROUPTYPE_ADD_SUCCESSFUL'])) {
        $successes[] = lang($m, $grouptypeDetails->name);
    }
}

#
# Display the form
#
echo $myForm->getHTML(['header', 'admin', 'footers', 'errors'=>$errors, 'successes'=>$successes]);
