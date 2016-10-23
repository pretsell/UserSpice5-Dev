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
if (!securePage($_SERVER['PHP_SELF'])){die();}


$errors = [];
$successes = [];

if(Input::exists('post')){
	if(!Token::check(Input::get('csrf'))){
		$tokenError = lang('TOKEN');
die($tokenError);
	}
}

$val_err = null;
$username = null;
$fname = null;
$lname = null;
$email = null;
$form_valid=FALSE;
$permOpsQ = $db->query("SELECT * FROM permissions");
$permOps = $permOpsQ->results();

//Forms posted
if (!empty($_POST)){
  //Delete User Checkboxes
  if (!empty($_POST['delete'])){
    $deletions = Input::get('delete');
    if ($deletion_count = deleteUsers($deletions)){
      $successes[] = "Account deletion successful";
    }
    else {
      $errors[] = "SQL Error";
    }
  }
}

$userData = fetchAllUsers(); //Fetch information for all users


?>
<div class="row">
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>	

	    <div class="col-xs-12 col-md-6">
		<h2>Manage Users</h2>
	  </div>

	<div class="col-xs-12 col-md-6">
	<form>
	<label for="system-search">Search:</label>
	<div class="input-group">
	<input class="form-control" id="system-search" name="q" placeholder="Search Users..." type="text">
	<span class="input-group-btn">
		<button type="submit" class="btn btn-default"><i class="fa fa-times"></i></button>
	</span>
	</div>
	</form>
	</div>

	</div>


		<div class="row">
		     <div class="col-md-12">
          <?php
		  echo display_errors($errors);
		  echo display_successes($successes);
		  ?>

	   </div>
	   </div>
        <div class="row">
        <div class="col-xs-12">
				 <div class="alluinfo">&nbsp;</div>
				<form name="adminUsers" action="admin_users.php" method="post">
				 <div class="allutable table-responsive">
					<table class='table table-hover table-list-search'>
					<thead>
					<tr>
						<th>Delete</th><th>Username</th><th>Email</th><th>First Name</th><th>Last Name</th><th>Join Date</th><th>Last Sign In</th><th>Logins</th>
					 </tr>
					</thead>
				 <tbody>
					<?php
					//Cycle through users
					foreach ($userData as $v1) {
							?>
					<tr>
					<td><div class="form-group"><input type="checkbox" name="delete[<?=$v1->id?>]" value="<?=$v1->id?>" /></div></td>
					<td><a href='admin_user.php?id=<?=$v1->id?>'><?=$v1->username?></a></td>
					<td><?=$v1->email?></td>
					<td><?=$v1->fname?></td>
					<td><?=$v1->lname?></td>
					<td><?=$v1->join_date?></td>
					<td><?=$v1->last_login?></td>
					<td><?=$v1->logins?></td>
					</tr>
							<?php } ?>

				  </tbody>
				</table>
				</div>
				<input class='btn btn-danger' type='submit' name='Submit' value='Delete' /><br><br>
				</form>
		  </div>
		</div>



	<!-- End of main content section -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->
<script src="js/search.js" charset="utf-8"></script>

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
