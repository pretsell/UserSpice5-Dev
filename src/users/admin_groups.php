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

//Forms posted
if(!empty($_POST))
{
  //Delete permission levels
  if(!empty($_POST['delete'])){
    $deletions = $_POST['delete'];
    if ($deletion_count = deletePermission($deletions)){
      $successes[] = "Permissions deletion was successful";
    }
  }

  //Create new permission level
  if(!empty($_POST['name'])) {
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
          $db->insert('permissions',$fields);
          $successes[]="Permission Updated";

		}else{
			/*
			Append validation errors to error array
			*/
			foreach ($validation->errors() as $error) {
				$errors[]=$error;
			}			
		}
  }
}


$permissionData = fetchAllPermissions(); //Retrieve list of all permission levels
$count = 0;
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
        <!-- Main Center Column -->


			<form name='adminPermissions' action='admin_permissions.php' method='post'>
			  <h2>Create a new Group</h2>
			  <p>
				<label>Group Name:</label>
				<input type='text' name='name' />
			  </p>

			  <br>
			  <table class='table table-hover table-list-search'>
				<tr>
				  <th>Delete</th><th>Group Name</th>
				</tr>

				<?php
				//List each permission level
				foreach ($permissionData as $v1) {
				  ?>
				  <tr>
					<td><input type='checkbox' name='delete[<?=$permissionData[$count]->id?>]' id='delete[<?=$permissionData[$count]->id?>]' value='<?=$permissionData[$count]->id?>'></td>
					<td><a href='admin_group.php?id=<?=$permissionData[$count]->id?>'><?=$permissionData[$count]->name?></a></td>
				  </tr>
				  <?php
				  $count++;
				}
				?>
			  </table>
			  <input type="hidden" name="csrf" value="<?=Token::generate();?>" >
			  <input class='btn btn-primary' type='submit' name='Submit' value='Add/Update/Delete' /><br><br>
			</form>

          <!-- End of main content section -->

      </div>
    </div>


    <!-- /.row -->

    <!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script src="js/search.js" charset="utf-8"></script>

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
