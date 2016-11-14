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
 * FORM: admin_grouptypes.PHP
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

checkToken();

# Initialize the validation data for validation and for form hints
$validation = new Validate([
    'grouptype_name' => [
        'action'=>'add',
        #'alias'=>'name'
    ],
    'grouptype_shortname' => [
        'action'=>'add',
        #'alias'=>'short_name'
    ]
]);

#
# Update the database with any form data in $_POST
#
if (Input::exists('post')) {
    if (@$_REQUEST['create'] || @$_POST['name']) {
        foreach ($validation->listFields() as $f) {
            $fields[$f] = Input::get($f);
        }
        if ($validation->check($fields)->passed()) {
            $db->insert('grouptypes', $fields);
            $successes[] = lang('GROUPTYPE_ADD_SUCCESS', $fields['name']);
        } else {
            $errors = $validation->stackErrorMessages($errors);
        }
    }
    if ($deletes = Input::get('deleteGrouptypes')) {
        deleteGrouptypes($deletes, $errors, $successes);
    }
}

#
# Prepare all data for displaying the form
#
$grouptypeData = $db->findAll('grouptypes', 'name')->results();

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
            <h2><?= lang('ADMINISTRATE_GROUP_TYPES_LABEL') ?></h2>
    		<?php resultBlock($errors, $successes); ?>
        </div> <!-- col -->
    </div> <!-- row -->
    <form method="post">
    <div class="row well">
        <div class="xs-col-12">
            <h3><?= lang('CREATE_GROUP_TYPE_LABEL') ?></h3>
        	<div class="form-group">
                <label><?= lang('GROUP_TYPE_NAME_LABEL') ?></label>
    			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('name') ?>"></span>
                <input class='form-control' type="text" name="name" >
            </div>
        	<div class="form-group">
                <label><?= lang('GROUP_TYPE_SHORT_NAME_LABEL') ?></label>
    			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('short_name') ?>"></span>
                <input class='form-control' type="text" name="short_name" >
            </div>
            <input type="submit" name="create" value="<?= lang('CREATE_GROUP_TYPE_LABEL') ?>" />
        </div> <!-- col -->
    </div> <!-- row -->
    <div class="row">
        <div class="xs-col-12">
            <table class="table table-hover">
                <tr><th><?= lang('DELETE_LABEL') ?></th><th><?= lang('GROUP_TYPE_NAME_LABEL') ?></th><th><?= lang('SHORT_NAME_LABEL') ?></th></tr>
                <?php
                foreach ($grouptypeData as $gt) {
                ?>
                    <tr>
                        <td><input type="checkbox" name="deleteGrouptypes[]" value="<?= $gt->id ?>" /></td>
                        <td><a href="admin_grouptype.php?id=<?= $gt->id ?>"><?= $gt->name ?></a></td>
                        <td><?= $gt->short_name ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
            <input type="submit" name="delete" value="<?= lang('DELETE_SELECTED_GROUP_TYPES_LABEL') ?>" />
        </div> <!-- col -->
    </div> <!-- row -->
    </form>
</div> <!-- container -->
