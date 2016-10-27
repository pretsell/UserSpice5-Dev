<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';

#Secures the page...required for page group/permission management
if (!securePage($_SERVER['PHP_SELF'])) { die(); }
checkToken();

$errors = [];
$successes = [];

# If requested group does not exist, redirect to admin_groups.php
if (!($groupId = @$_GET['id']) || !groupIdExists($groupId)) {
	Redirect::to("admin_groups.php"); die();
}

$validation = new Validate(
	['groupname'=>
			['alias'=>'name',
			 'action'=>'update',
			 'update_id'=>$groupId]]
);
//Fetch information specific to this group
$groupDetails = fetchGroupDetails($groupId);
//Forms posted
if (Input::exists('post')) {
  //Delete selected group
  if ($deletions = Input::get('delete', 'post')) {
    if ($deletion_count = deleteGroups($deletions)) {
      $successes[] = lang("GROUP_DELETIONS_SUCCESSFUL", array($deletion_count));
      Redirect::to('admin_groups.php');
    }
    else {
      $errors[] = "SQL Error";
    }
  } else {
    //Update group name
    if ($groupDetails['name'] != $_POST['name']) {
      $group_name = Input::get('name');
      $fields=array('name'=>$group_name);
      $validation->check($_POST);
      if ($validation->passed()) {
        $db->update('groups',$groupId,$fields);
				$successes[] = lang('GROUP_NAME_UPDATE', $group_name);
      } else {
				$errors = $validation->stackErrorMessages($errors);
      }
    }

    //Remove user(s) from group
    if ($remove = Input::get('removeUsers', 'post')) {
      if ($deletion_count = deleteGroupsUsers_raw($groupId, $remove)) {
        $successes[] = lang("GROUP_REMOVE_USERS", array($deletion_count));
      } else {
        $errors[] = lang("SQL_ERROR");
      }
    }

    //Remove nested group(s) from group
    if ($remove = $_POST['removeGroupGroups']) {
      if ($deletion_count = deleteGroupsUsers_raw($groupId, $remove, 1)) {
        $successes[] = lang("GROUP_REMOVE_GROUPS", array($deletion_count));
      } else {
        $errors[] = lang("SQL_ERROR");
      }
    }

    //Add users to group
    if ($add = Input::get('addUsers', 'post')) {
      if ($addition_count = addGroupsUsers_raw($groupId, $add)) {
        $successes[] = lang("GROUP_ADD_USERS", array($addition_count));
      } else {
        $errors[] = lang("SQL_ERROR");
      }
    }

    //Add nested groups to group
    if ($add = Input::get('addGroupGroups', 'post')) {
      if ($addition_count = addGroupsUsers_raw($groupId, $add, 1)) {
        $successes[] = lang("GROUP_ADD_GROUPS", array($addition_count));
      } else {
        $errors[] = lang("SQL_ERROR");
      }
    }

    //Remove pages from group
    if ($remove = Input::get('removePage', 'post')) {
      if ($deletion_count = deleteGroupsPages($remove, $groupId)) {
        $successes[] = lang("GROUP_REMOVE_PAGES", array($deletion_count));
      } else {
        $errors[] = lang("SQL_ERROR");
      }
    }

    //Add access to pages
    if ($add = Input::get('addPage', 'post')) {
      if ($addition_count = addGroupsPages($add, $groupId)) {
        $successes[] = lang("GROUP_ADD_PAGES", array($addition_count));
      } else {
        $errors[] = lang("SQL_ERROR");
      }
    }
    $groupDetails = fetchGroupDetails($groupId);
  }
}

//Retrieve list of accessible pages
$groupPages = fetchPagesByGroup($groupId);

//Retrieve list of users with and without membership
$groupMembers = fetchGroupMembers_raw($groupId);
$nonGroupMembers = fetchGroupMembers_raw($groupId,true);
// dump($groupMembers);
// dump($nonGroupMembers);

//Fetch all users
$userData = fetchAllUsers();


//Fetch all pages
$pageData = fetchAllPages();

?>

    <div class="row">
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
      <div class="col-xs-12">
		<?php
		echo display_errors($errors);
		echo display_successes($successes);

		?>
          <h1>Configure Details for this Group</h1>

			<form name='adminGroup' action='admin_group.php?id=<?=$groupId?>' method='post'>
			<table class='table'>
			<tr><td>
			<h3>Group Information</h3>
			<div id='regbox'>
			<p>
			<label>ID:</label>
			<?=$groupDetails['id']?>
			</p>
			<p>
			<label>Name:</label>
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('name') ?>"></span>
			<br />
			<input type='text' name='name' value='<?=$groupDetails['name']?>' />
			</p>
			<h3>Delete this Group?</h3>
			<label>Delete:</label>
			<input type='checkbox' name='delete[<?=$groupDetails['id']?>]' id='delete[<?=$groupDetails['id']?>]' value='<?=$groupDetails['id']?>'>
			</p>
			</div></td><td>
			<h3>Group Membership</h3>
			<div id='regbox'>
			<p><strong>
      Remove Members:</strong>
      <br />Users:
			<?php
			//Display list of groups with access
      $nested =  false;
			foreach($groupMembers as $gm) {
        if ($gm->group_or_user == 'group') {
          $nested = true;
          continue;
        }
				echo "<br><label><input type='checkbox' name='removeUsers[]' id='removeUsers[]' value='$gm->id'> $gm->name</label>\n";
			}
      if ($nested) {
        echo "<br />Nested Groups:";
  			foreach($groupMembers as $gm) {
          if ($gm->group_or_user != 'group') {
            continue;
          }
  				echo "<br><label><input type='checkbox' name='removeGroupGroups[]' id='removeGroupGroups[]' value='$gm->id'> $gm->name</label>\n";
				}
			}
			?>
      </p>
      <p><strong>
			Add Members:</strong>
      <br />Users:
			<?php
			//List users NOT in this group
      $nested = false;
			foreach($nonGroupMembers as $ngm) {
        if ($ngm->group_or_user == 'group') {
          $nested = true;
          continue;
        }
				echo "<br><label><input type='checkbox' name='addUsers[]' id='addUsers[]' value='$ngm->id'> $ngm->name</label>\n";
			}
      if ($nested) {
        echo "<br />Nested Groups:";
  			foreach($nonGroupMembers as $ngm) {
          if ($ngm->group_or_user != 'group') {
            continue;
          }
  				echo "<br><label><input type='checkbox' name='addGroupGroups[]' id='addGroupGroups[]' value='$ngm->id'> $ngm->name</label>\n";
        }
      }
			?>

			</div>
			</td>
			<td>
			<h3>Page Access</h3>
			<div id='regbox'>
			<p><br><strong>
			Public Pages:</strong>
			<?php
			//List public pages
			foreach ($pageData as $v1) {
			  if ($v1->private != 1) {
				echo "<br>".$v1->page;
			  }
			}
			?>
			</p>
			<p><br><strong>
			Remove Page Access From This Group:</strong>
			<?php
			//Display list of pages with this group
			$page_ids = [];
			foreach($groupPages as $gp) {
			  $page_ids[] = $gp->page_id;
			}
			foreach ($pageData as $v1) {
			  if (in_array($v1->id,$page_ids)) { ?>
				<br><input type='checkbox' name='removePage[]' id='removePage[]' value='<?=$v1->id;?>'> <?=$v1->page;?>
			  <?php }
			}  ?>
			</p>
			<p><br><strong>
			Add Access To This Level:</strong>
			<?php
			//Display list of pages with this access level

			foreach ($pageData as $v1) {
			  if (!in_array($v1->id,$page_ids) && $v1->private == 1) { ?>
				<br><input type='checkbox' name='addPage[]' id='addPage[]' value='<?=$v1->id;?>'> <?=$v1->page;?>
			  <?php }
			}  ?>
			</p>
			</div>
			</td>
			</tr>
			</table>
			<input type="hidden" name="csrf" value="<?=Token::generate();?>" >
			<p>
			<label>&nbsp;</label>
			<input class='btn btn-primary' type='submit' value='Update Group' class='submit' />
			</p>
			</form>
      </div>
    </div>
    <!-- /.row -->
    <!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
