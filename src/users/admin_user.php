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

/*
If $_POST data exists, then check CSRF token, and kill page if not correct...no need to process rest of page or form data
*/
if (Input::exists()) {
	if (!Token::check(Input::get('csrf'))) {
		die('Token doesn\'t match!');
	}
}

$validation = new Validate();
$errors = $successes = [];
$userId = Input::get('id');
//Check if selected user exists
if (!userIdExists($userId)) {
  Redirect::to("admin_users.php"); die();
}

$userdetails = fetchUserDetails(NULL, NULL, $userId); //Fetch user details

//Forms posted
if (!empty($_POST)) {

  //Update username

  if ($userdetails->username != $_POST['username']) {
    $username = Input::get("username");

    $fields=array('username'=>$username);
    $validation->check($_POST,array(
      'username' => array(
        'display' => 'Username',
        'required' => true,
        'unique_update' => 'users,'.$userId,
        'min' => 1,
        'max' => 150
      )
    ));
  if ($validation->passed()) {
    $db->update('users',$userId,$fields);
    $successes[] = "Username Updated";
  } else {
  foreach ($validation->errors() as $error) {
    $errors[] = $error;
  }
    }
  }

  //Update first name

  if ($userdetails->fname != $_POST['fname']) {
     $fname = Input::get("fname");

    $fields=array('fname'=>$fname);
    $validation->check($_POST,array(
      'fname' => array(
        'display' => 'First Name',
        'required' => true,
        'min' => 1,
        'max' => 150
      )
    ));
  if ($validation->passed()) {
    $db->update('users',$userId,$fields);
    $successes[] = "First Name Updated";
  } else {
  foreach ($validation->errors() as $error) {
    $errors[] = $error;
  }
    }
  }

  //Update last name

  if ($userdetails->lname != $_POST['lname']) {
    $lname = Input::get("lname");

    $fields=array('lname'=>$lname);
    $validation->check($_POST,array(
      'lname' => array(
        'display' => 'Last Name',
        'required' => true,
        'min' => 1,
        'max' => 150
      )
    ));
  if ($validation->passed()) {
    $db->update('users',$userId,$fields);
    $successes[] = "Last Name Updated";
  } else {
  foreach ($validation->errors() as $error) {
    $errors[] = $error;
  }
    }
  }

  //Block User
  if ($userdetails->permissions != $_POST['active']) {
    $active = Input::get("active");
    $fields=array('permissions'=>$active);
    $db->update('users',$userId,$fields);
  }

  //Update email
  if ($userdetails->email != $_POST['email']) {
    $email = Input::get("email");
    $fields=array('email'=>$email);
    $validation->check($_POST,array(
      'email' => array(
        'display' => 'Email',
        'required' => true,
        'valid_email' => true,
        'unique_update' => 'users,'.$userId,
        'min' => 3,
        'max' => 150
      )
    ));
  if ($validation->passed()) {
    $db->update('users',$userId,$fields);
    $successes[] = "Email Updated";
  } else {
  foreach ($validation->errors() as $error) {
    $errors[] = $error;
  }
    }
  }

  //Remove group(s)
  if (!empty($_POST['removeGroup'])) {
    $remove = Input::get('removeGroup');
    if ($deletion_count = deleteGroupsUsers_raw($remove, $userId)) {
      $successes[] = lang("ACCOUNT_GROUP_REMOVED", array ($deletion_count));
    } else {
      $errors[] = lang("SQL_ERROR");
    }
  }

  if (!empty($_POST['addGroup'])) {
    $add = Input::get('addGroup');
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
//
?>
<div class="row">
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
	<div class="col-xs-12 col-md-3"><!--left col-->
	<?php echo $useravatar;?>
	</div><!--/col-2-->

	<div class="col-xs-12 col-md-9">


	<h3>User Information</h3>

	<?php
	echo display_errors($errors);
	echo display_successes($successes);
	?>
	<form class="form" name='adminUser' action='admin_user.php?id=<?=$userId?>' method='post'>

	<div class="form-group">
		<label>User ID </label>
		<input  class='form-control' type='text' name='username' value='<?=$userdetails->id?>' readonly/>
	</div>
	<div class="form-group">
		<label>Joined </label>
		<input  class='form-control' type='text' name='username' value='<?=$userdetails->join_date?>' readonly/>
	</div>
	<div class="form-group">
		<label>Last Login</label>
		<input  class='form-control' type='text' name='username' value='<?=$userdetails->last_login?>' readonly/>
	</div>
	<div class="form-group">
		<label>Logins </label>
		<input  class='form-control' type='text' name='username' value='<?=$userdetails->logins?>' readonly/>
	</div>
	<div class="form-group">
		<label>Username</label>
		<input  class='form-control' type='text' name='username' value='<?=$userdetails->username?>' />
	</div>
	<div class="form-group">
		<label>Email</label>
		<input class='form-control' type='text' name='email' value='<?=$userdetails->email?>' />
	</div>
	<div class="form-group">
		<label>First Name</label>
		<input  class='form-control' type='text' name='fname' value='<?=$userdetails->fname?>' />
	</div>
	<div class="form-group">
		<label>Last Name</label>
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
		<select name="active" class="form-control">
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
