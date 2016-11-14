<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])) { die(); }
checkToken();

$errors = $successes = [];
if (isset($mode) && $mode == 'role') {
    $modeName = 'Group Role';
} else {
    $modeName = 'Group';
    $mode = 'normal';
}

$validation = new Validate([
	'groupname'      => [ 'action'=>'add', ],
    'groupshortname' => [ 'action' => 'add', ],
]);

//Forms posted
if(Input::exists('post')) {
    //Delete groups
    if($deletions = Input::get('delete', 'post')) {
        if ($deletion_count = deleteGroups($deletions)) {
            $successes[] = lang("GROUP_DELETIONS_SUCCESSFUL", array($deletion_count));
        }
    }

    //Create new group
    if(!empty($_POST['name']) || !empty($_POST['create'])) {
        #var_dump($_POST);
        foreach (['name', 'short_name'] as $f) {
            $fields[$f] = Input::get($f);
        }
        $validation->check($_POST);
        if($validation->passed()) {
            $db->insert('groups',$fields);
            $successes[]=lang("GROUP_ADD_SUCCESSFUL", $fields['name']);
        } else {
			$errors = $validation->stackErrorMessages($errors);
        }
    }
}

$groupData = fetchAllGroups(); //Retrieve list of all groups
$hasRoles = false;
foreach ($groupData as $g) {
    if ($g->is_role) {
        $hasRoles = true;
        break;
    }
}
$groupTypes = fetchAllGroupTypes(); // return in array
$count = 0;
// dump($groupData);
// echo $groupData[0]->name;
?>

<div class="container">
    <div class="row">
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=configGet('version')?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
      <div class="col-xs-12">
		<?php
		resultBlock($errors, $successes);
		?>

		<form name='adminGroups' action='admin_groups.php' method='post'>
            <input type="hidden" name="mode" value="<?= $mode ?>" />
        <div>
        <h2>Administrate <?= $modeName ?>s</h2>
        <div class="well">
		  <h4>Create a new <?= $modeName ?></h4>
			<label><?= $modeName ?> Name:</label>
			<input type='text' name='name' />
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('name') ?>"></span>&nbsp;&nbsp;
			<label>Short Name:</label>
			<input type='text' name='short_name' />
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('short_name') ?>"></span>&nbsp;&nbsp;
			<input class='btn btn-primary' type='submit' name='create' value='Create' /><br><br>
        </div>
		<br>
		<table class='table table-hover table-list-search'>
        <tr>
            <th colspan="3"><h3><?= $mode == 'role' ? 'Group Roles' : 'Groups' ?></h3></th>
        </tr>
		<tr>
	  	    <th>Delete</th><th><?= $modeName ?> Name</th><th>Short Name</th><th>Group Type</th>
            <?php
            if ($hasRoles && $mode != 'role') {
            ?>
                <th>Roles</th>
            <?php
            }
            ?>
		</tr>
		<?php
		//List each group where is_role==n  n=0:(normal groups)/n=1:(role group)
        $roleVal = (($mode == 'role') ? 1 : 0);
		foreach ($groupData as $g) {
            if ($g->is_role == $roleVal) { ?>
				<tr>
					<td><input type='checkbox' name='delete[<?=$g->id?>]' value='<?=$g->id?>' ></td>
					<td><a href='<?= $mode == 'role' ? 'admin_role.php' : 'admin_group.php' ?>?id=<?=$g->id?>'><?=$g->name?></a></td>
					<td><?=$g->short_name?></td>
					<td><a href='admin_grouptypes.php?id=<?=$g->grouptype_id?>'><?=findValInArrayOfObject($groupTypes, 'id', $g->grouptype_id, 'name')?></a></td>
                    <td>
                    <?php
                    if ($hasRoles && $mode != 'role') {
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
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script src="js/search.js" charset="utf-8"></script>

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
