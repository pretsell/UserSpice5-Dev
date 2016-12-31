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
    'createWell' => new Form_Well ([
        'createForm' => new Form_Form ([
            '<h3>'.lang('CREATE_'.$mode).'</h3>',
            'name' => new FormField_Text ([
                'dbfield' => 'groups.name',
                #'debug' => 3,
                'new_valid' => ['action'=>'add'],
            ]),
            'short_name' => new FormField_Text ([
                'dbfield' => 'groups.short_name',
                #'debug' => 3,
                'new_valid' => ['action'=>'add'],
            ]),
            'create' => new FormField_ButtonSubmit ([
                'name' => 'create',
                'display' => lang('CREATE_'.$mode),
            ]),
        ], ['Form_Name' => 'createForm']),
    ], ['Well_Class' => 'well-sm']),
    'deleteGroupForm' => new Form_Form ([
        '<h4>'.lang($mode.'_LIST_TITLE').'</h4>',
        'groupList' => new FormField_Table ([
            'field' => 'deleteGroups',
            'isdbfield' => false,
            'table_head_cells' => '<th>'.lang($mode.'_DELETE').'</th>'.
                '<th>'.lang($mode.'_NAME').'</th>'.
                '<th>'.lang($mode.'_SHORT_NAME').'</th>'.
                '<th>'.lang('GROUPTYPE_NAME_LABEL').'</th>'.
                ($mode == 'ROLE' ? '' : '<th>'.lang('ROLE_LIST_TITLE').'</th>'),
            'table_data_cells' => '<td>{CHECKBOX}</td>'.
                '<td><a href="'.$childForm.'?id={ID}">{NAME}</a></td>'.
                '<td>{SHORT_NAME}</td>'.
                '<td><a href="admin_grouptypes.php?id={grouptype_id}">{GROUPTYPE_NAME}</a></td>'.
                ($mode == 'ROLE' ? '' : '<td>{ROLES}</td>'  ),
            'nodata' => '<p>'.lang('NO_ROLES_ASSIGNED').'</p>',
            'Table_Class' => 'table table-bordered table-condensed',
        ], ['Form_Name' => 'deleteGroupForm']),
        'save' => new FormField_ButtonSubmit ([
            'display' => 'Delete Selected',
        ])
    ], ['Form_Name' => 'deleteForm']),
], [
    'title' => lang('ADMINISTRATE_'.$mode),
    'exclude_elements' => ['openForm', 'closeForm'],
    'dbtable' => 'groups',
]);

//Forms posted
if(Input::exists('post')) {
    //Delete groups
    if($deletions = Input::get('deleteGroups', 'post')) {
        if ($deletion_count = deleteGroups($deletions, $errors)) {
            $successes[] = lang("GROUP_DELETIONS_SUCCESSFUL", array($deletion_count));
        }
    }

    //Create new group
    if(!empty($_POST['name']) || !empty($_POST['create'])) {
        #var_dump($_POST);
        $myForm->setNewValues($_POST);
        if ($myForm->insertIfValid($errors)) {
            $successes[] = lang('GROUP_ADD_SUCCESSFUL', $myForm->getField('name')->getNewValue());
        }
    }
}

//List each group where is_role==n  n=0:(normal groups)/n=1:(role group)
$groups = fetchAllGroups(); //Retrieve list of all groups
$roleVal = (($mode == 'ROLE') ? 1 : 0);
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
?>

<div class="container">
    <div class="row">
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=configGet('version')?></h1>
	<!-- ?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ? -->
	</div>
      <div class="col-xs-12">
		<?php
		resultBlock($errors, $successes);
		?>

		<form name='adminGroups' action='admin_groups.php' method='post'>
            <input type="hidden" name="mode" value="<?= $mode ?>" />
        <div>
        <h2><?= lang('ADMINISTRATE_'.$mode) ?></h2>
        <div class="well">
		  <h4>Create a new <?= lang('CREATE_'.$mode) ?></h4>
			<label><?= lang($mode.'_NAME') ?>: </label>
			<input type='text' name='name' />
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('name') ?>"></span>&nbsp;&nbsp;
			<label><?= lang($mode.'_SHORT_NAME') ?>: </label>
			<input type='text' name='short_name' />
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('short_name') ?>"></span>&nbsp;&nbsp;
			<input class='btn btn-primary' type='submit' name='create' value='Create' /><br><br>
        </div>
		<br>
		<table class='table table-hover table-list-search'>
        <tr>
            <th colspan="3"><h3><?= lang($mode.'_LIST_TITLE') ?></h3></th>
        </tr>
		<tr>
	  	    <th><?= lang('DELETE') ?></th><th><?= lang($mode.'_NAME') ?></th><th><?= lang($mode.'_SHORT_NAME') ?></th><th><?= lang('GROUP_TYPE') ?></th>
            <?php
            if ($hasRoles && $mode != 'ROLE') {
            ?>
                <th>Roles</th>
            <?php
            }
            ?>
		</tr>
		<?php
		//List each group where is_role==n  n=0:(normal groups)/n=1:(role group)
        $roleVal = (($mode == 'ROLE') ? 1 : 0);
		foreach ($groupData as $g) {
            if ($g->is_role == $roleVal) { ?>
				<tr>
					<td><input type='checkbox' name='delete[<?=$g->id?>]' value='<?=$g->id?>' ></td>
					<td><a href='<?= $mode == 'ROLE' ? 'admin_role.php' : 'admin_group.php' ?>?id=<?=$g->id?>'><?=$g->name?></a></td>
					<td><?=$g->short_name?></td>
					<td><a href='admin_grouptypes.php?id=<?=$g->grouptype_id?>'><?=findValInArrayOfObject($groupTypes, 'id', $g->grouptype_id, 'name')?></a></td>
                    <td>
                    <?php
                    if ($hasRoles && $mode != 'ROLE') {
                        $sep = '';
                        foreach (fetchRolesByGroup($g->id) as $role) {
                            echo $sep.'<span style="font-weight: bold">'.$role->short_name.'</span>: '.$role->fname.' '.$role->lname.' ('.$role->username.')';
                            $sep = ', ';
                        }
                    } ?>
                    </td>
				</tr>
				<?php
				$count++;
            }
		}
		?>
		</table>
		<input type="hidden" name="csrf" value="<?=Token::generate();?>" >
		<input class='btn btn-primary' type='submit' name='Submit' value='Save Changes' /><br><br>
		</form>

          <!-- End of main content section -->

      </div>
    </div>
</div>


    <!-- /.row -->

    <!-- footers -->
<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script src="js/search.js" charset="utf-8"></script>

<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
