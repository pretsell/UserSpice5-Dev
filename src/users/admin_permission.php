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
		die('Token doesn\'t match!');
	}
}

$validation = new Validate();
//PHP Goes Here!
$permissionId = $_GET['id'];

/*
If requested permission level does not exist, redirect to admin_permissions.php
*/
if(!permissionIdExists($permissionId)){
Redirect::to("admin_permissions.php"); die();
}

//Fetch information specific to permission level
$permissionDetails = fetchPermissionDetails($permissionId);
//Forms posted
if(Input::exists()){
  //Delete selected permission level
  if(!empty($_POST['delete'])){
    $deletions = Input::get('delete');
    if ($deletion_count = deletePermission($deletions)){
      $successes[] = "Permission deletion successful";
      Redirect::to('admin_permissions.php');
    }
    else {
      $errors[] = "SQL Error";
    }
  }
  else
  {
    //Update permission level name
    if($permissionDetails['name'] != $_POST['name']) {
      $permission = Input::get('name');
      $fields=array('name'=>$permission);
//NEW Validations
    $validation->check($_POST,array(
      'name' => array(
        'display' => 'Permission Name',
        'required' => true,
        'unique' => 'permissions',
        'min' => 1,
        'max' => 25
      )
    ));
    if($validation->passed()){
      $db->update('permissions',$permissionId,$fields);

    }else{
			/*
			Append validation errors to error array
			*/
			foreach ($validation->errors() as $error) {
				$errors[]=$error;
			}			
        }
      }

    //Remove access to pages
    if(!empty($_POST['removePermission'])){
      $remove = $_POST['removePermission'];
      if ($deletion_count = removePermission($permissionId, $remove)) {
        $successes[] = "Removed user permission";
      }
      else {
        $errors[] = "SQL error";
      }
    }

    //Add access to pages
    if(!empty($_POST['addPermission'])){
      $add = $_POST['addPermission'];
      if ($addition_count = addPermission($permissionId, $add)) {
        $successes[] = "Added user permission";
      }
      else {
        $errors[] = "SQL error";
      }
    }

    //Remove access to pages
    if(!empty($_POST['removePage'])){
      $remove = $_POST['removePage'];
      if ($deletion_count = removePage($remove, $permissionId)) {
        $successes[] = "Removed page permission";
      }
      else {
        $errors[] = "SQL error";
      }
    }

    //Add access to pages
    if(!empty($_POST['addPage'])){
      $add = $_POST['addPage'];
      if ($addition_count = addPage($add, $permissionId)) {
        $successes[] = "Added page permission";
      }
      else {
        $errors[] = "SQL error";
      }
    }
    $permissionDetails = fetchPermissionDetails($permissionId);
  }
}

//Retrieve list of accessible pages
$pagePermissions = fetchPermissionPages($permissionId);




  //Retrieve list of users with membership
$permissionUsers = fetchPermissionUsers($permissionId);
// dump($permissionUsers);

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
          <h1>Configure Details for this Permission Level</h1>

			<form name='adminPermission' action='admin_permission.php?id=<?=$permissionId?>' method='post'>
			<table class='table'>
			<tr><td>
			<h3>Permission Information</h3>
			<div id='regbox'>
			<p>
			<label>ID:</label>
			<?=$permissionDetails['id']?>
			</p>
			<p>
			<label>Name:</label>
			<input type='text' name='name' value='<?=$permissionDetails['name']?>' />
			</p>
			<h3>Delete this Level?</h3>
			<label>Delete:</label>
			<input type='checkbox' name='delete[<?=$permissionDetails['id']?>]' id='delete[<?=$permissionDetails['id']?>]' value='<?=$permissionDetails['id']?>'>
			</p>
			</div></td><td>
			<h3>Permission Membership</h3>
			<div id='regbox'>
			<p><strong>
			Remove Members:</strong>
			<?php
			//Display list of permission levels with access
			$perm_users = [];
			foreach($permissionUsers as $perm){
			  $perm_users[] = $perm->user_id;
			}
			foreach ($userData as $v1){
			  if(in_array($v1->id,$perm_users)){ ?>
				<br><input type='checkbox' name='removePermission[]' id='removePermission[]' value='<?=$v1->id;?>'> <?=$v1->username;
			}
			}
			?>

			</p><strong>
			<p>Add Members:</strong>
			<?php
			//List users without permission level
			$perm_losers = [];
			foreach($permissionUsers as $perm){
			  $perm_losers[] = $perm->user_id;
			}
			foreach ($userData as $v1){
				if(!in_array($v1->id,$perm_losers)){ ?>
				<br><input type='checkbox' name='addPermission[]' id='addPermission[]' value='<?=$v1->id?>'> <?=$v1->username;
			}
			}
			?>

			</p>
			</div>
			</td>
			<td>
			<h3>Permission Access</h3>
			<div id='regbox'>
			<p><br><strong>
			Public Pages:</strong>
			<?php
			//List public pages
			foreach ($pageData as $v1) {
			  if($v1->private != 1){
				echo "<br>".$v1->page;
			  }
			}
			?>
			</p>
			<p><br><strong>
			Remove Access From This Level:</strong>
			<?php
			//Display list of pages with this access level
			$page_ids = [];
			foreach($pagePermissions as $pp){
			  $page_ids[] = $pp->page_id;
			}
			foreach ($pageData as $v1){
			  if(in_array($v1->id,$page_ids)){ ?>
				<br><input type='checkbox' name='removePage[]' id='removePage[]' value='<?=$v1->id;?>'> <?=$v1->page;?>
			  <?php }
			}  ?>
			</p>
			<p><br><strong>
			Add Access To This Level:</strong>
			<?php
			//Display list of pages with this access level

			foreach ($pageData as $v1){
			  if(!in_array($v1->id,$page_ids) && $v1->private == 1){ ?>
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
			<input class='btn btn-primary' type='submit' value='Update Permission' class='submit' />
			</p>
			</form>
      </div>
    </div>
    <!-- /.row -->
    <!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
