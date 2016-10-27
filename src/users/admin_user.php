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
$userId = Input::get('id');
//Check if selected user exists
if (!userIdExists($userId)) {
  Redirect::to("admin_users.php"); die();
}

$userdetails = fetchUserDetails(NULL, NULL, $userId); //Fetch user details
$validation = new Validate([
		'username' => ['action'=>'update', 'update_id'=>$userId],
		'fname',
		'lname',
		'email' => ['action'=>'update', 'update_id'=>$userId]]);

//Forms posted
if (Input::exists('post')) {

  $validation->check($_POST);
  if ($validation->passed()) {
		foreach (['username', 'fname', 'lname', 'permissions', 'email'] as $f) {
			$fields[$f] = Input::get($f, 'post');
		}
    $db->update('users',$userId,$fields);
    $successes[] = lang("ACCOUNT_DETAILS_UPDATED");
  } else {
		$errors = $validation->stackErrorMessages($errors);
  }

  //Remove group(s)
  if ($remove = Input::get('removeGroup')) {
    if ($deletion_count = deleteGroupsUsers_raw($remove, $userId)) {
      $successes[] = lang("ACCOUNT_GROUP_REMOVED", array ($deletion_count));
    } else {
      $errors[] = lang("SQL_ERROR");
    }
  }

  if ($add = Input::get('addGroup')) {
    if ($addition_count = addGroupsUsers_raw($add, $userId,'user')) {
      $successes[] = lang("ACCOUNT_GROUP_ADDED", array ($addition_count));
    } else {
      $errors[] = "SQL error";
    }
  }

  $userdetails = fetchUserDetails(NULL, NULL, $userId);
}

$userGroups = fetchUserGroups($userId);
$groupsData = fetchAllGroups();

$grav = get_gravatar(strtolower(trim($userdetails->email)));
$useravatar = '<img src="'.$grav.'" class="img-responsive img-thumbnail" alt="">';
?>
<div class="row">
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
	<div class="col-xs-12 col-md-3"><!--left col-->
	<?php echo $useravatar;?>
	<form class="form" name='adminUser' action='admin_user.php?id=<?=$userId?>' method='post'>

	<br />
	<div class="form-group">
		<label>User ID </label>
		<input class='form-control' type='text' name='username' value='<?=$userdetails->id?>' readonly/>
	</div>
	<div class="form-group">
		<label>Joined </label>
		<input  class='form-control' type='text' name='join_date' value='<?=$userdetails->join_date?>' readonly/>
	</div>
	<div class="form-group">
		<label>Last Login</label>
		<input  class='form-control' type='text' name='last_login' value='<?=$userdetails->last_login?>' readonly/>
	</div>
	<div class="form-group">
		<label>Logins </label>
		<input  class='form-control' type='text' name='logins' value='<?=$userdetails->logins?>' readonly/>
	</div>
	</div><!--/col-2-->

	<div class="col-xs-12 col-md-9">

	<h3>User Information</h3>

	<?php
	resultBlock($errors, $successes);
	?>
	<div class="form-group">
		<label>Username</label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('username') ?>"></span>
		<input  class='form-control' type='text' name='username' value='<?=$userdetails->username?>' />
	</div>
	<div class="form-group">
		<label>Email</label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('email') ?>"></span>
		<input class='form-control' type='text' name='email' value='<?=$userdetails->email?>' />
	</div>
	<div class="form-group">
		<label>First Name</label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('fname') ?>"></span>
		<input  class='form-control' type='text' name='fname' value='<?=$userdetails->fname?>' />
	</div>
	<div class="form-group">
		<label>Last Name</label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('lname') ?>"></span>
		<input  class='form-control' type='text' name='lname' value='<?=$userdetails->lname?>' />
	</div>

	<h3>Groups</h3>
	<div class="panel panel-default">
		<div class="panel-heading">Remove User From These Groups(s):</div>
		<div class="panel-body">
		<?php
		//NEW List of groups user is a part of

		$group_ids = [];
		foreach($userGroups as $group) {
			$group_ids[] = $group->group_id;
		}

		foreach ($groupsData as $v1) {
		if (in_array($v1->id,$group_ids)) { ?>
		  <label><input type='checkbox' name='removeGroup[]' id='removeGroup[]' value='<?=$v1->id;?>' /> <?=$v1->name;?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?php
		}
		}
		?>

		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">Add These Group(s):</div>
		<div class="panel-body">
		<?php
		foreach ($groupsData as $v1) {
			if (!in_array($v1->id,$group_ids)) { ?>
			  <label><input type='checkbox' name='addGroup[]' id='addGroup[]' value='<?=$v1->id;?>' /> <?=$v1->name;?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?php
			}
		}
		?>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">Miscellaneous:</div>
		<div class="panel-body">
		<label> Block?</label>
		<select name="permissions" class="form-control">
			<option <?php if ($userdetails->permissions==1) {echo "selected='selected'";} ?> value="1">No</option>
			<option <?php if ($userdetails->permissions==0) {echo "selected='selected'";} ?>value="0">Yes</option>
		</select>
		</div>
	</div>

	<input type="hidden" name="csrf" value="<?=Token::generate();?>" />
	<input class='btn btn-primary' type='submit' value='Update' class='submit' />
	<a class='btn btn-warning' href="admin_users.php">Cancel</a><br><br>

	</form>

	</div><!--/col-9-->
</div><!--/row-->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
