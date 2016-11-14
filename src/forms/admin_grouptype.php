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

# Initialize the validation data for validation and for form hints
$validation = new Validate([
    'grouptype_name' => [
        'action'=>'update',
        'update_id'=>$grouptype_id,
    ],
    'grouptype_shortname' => [
        'action'=>'update',
        'update_id'=>$grouptype_id,
    ]
]);

#
# Update the database with any form data in $_POST
#
if (Input::exists('post')) {
    $curvals = $db->findById('grouptypes', $grouptype_id)->first();
    $changed = false;
    foreach ($validation->listFields() as $f) {
        $fields[$f] = Input::get($f);
        if ($fields[$f] != $curvals->$f) {
            $changed = true;
        }
    }
    if ($changed) {
        if ($validation->check($fields)->passed()) {
            $db->update('grouptypes', $grouptype_id, $fields);
            $successes[] = lang('GROUPTYPE_UPDATE_SUCCESS', $fields['name']);
        } else {
            $errors = $validation->stackErrorMessages($errors);
        }
    }
    if ($deletes = Input::get('deleteGrouptype')) {
        deleteGrouptypes($deletes, $errors, $successes);
    }
}

#
# Prepare all data for displaying the form
#
$grouptypeData = $db->findById('grouptypes', $grouptype_id)->first();

#
# Display the form
#
?>
<div class="container">
    <div class="row">
        <div class="xs=col-12">
            <?php include_once(ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_dashboard.php'); ?>
        </div> <!-- col -->
        <div class="xs=col-12">
            <h2><?= lang('EDIT_GROUP_TYPE_LABEL') ?></h2>
    		<?php resultBlock($errors, $successes); ?>
        </div> <!-- col -->
    </div> <!-- row -->
    <form method="post">
    <div class="row">
        <div class="xs-col-12">
        	<div class="form-group">
                <label><?= lang('GROUP_TYPE_NAME_LABEL') ?></label>
    			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('name') ?>"></span>
                <br />
                <input class='form-control' type="text" name="name" value="<?= $grouptypeData->name ?>">
                <br />
            </div>
        	<div class="form-group">
                <label><?= lang('GROUP_TYPE_SHORT_NAME_LABEL') ?></label>
    			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('short_name') ?>"></span>
                <br />
                <input class='form-control' type="text" name="short_name" value="<?= $grouptypeData->short_name ?>">
                <br />
            </div>
    		<input class='btn btn-primary' type='submit' value="<?= lang('SAVE_GROUP_TYPE_LABEL') ?>" class='submit' />
    		<button class="btn btn-primary btn-danger" name="deleteGrouptype" value="<?= $grouptype_id ?>"><?= lang('DELETE_GROUP_TYPE_LABEL') ?></button>
        </div> <!-- col -->
    </div> <!-- row -->
    </form>
</div> <!-- container -->
