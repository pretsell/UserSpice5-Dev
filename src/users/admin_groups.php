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


$errors = [];
$successes = [];

if(Input::exists('post')) {
	if(!Token::check(Input::get('csrf'))) {
		die('Token doesn\'t match!');
	}
}
$validation = new Validate(
	['groupname'=>
			['alias'=>'name',
			 'action'=>'add' ]]
);

//Forms posted
if(Input::exists('post')) {
  //Delete groups
  if(!empty($_POST['delete'])) {
    $deletions = Input::get('delete', 'post');
    if ($deletion_count = deleteGroups($deletions)) {
      $successes[] = lang("GROUP_DELETIONS_SUCCESSFUL", array($deletion_count));
    }
  }

  //Create new group
  if(!empty($_POST['name'])) {
    $groupName = Input::get('name', 'post');
    $fields=array('name'=>$groupName);
    $validation->check($_POST);
    if($validation->passed()) {
      $db->insert('groups',$fields);
      $successes[]=lang("GROUP_ADD_SUCCESSFUL", $groupName);
    } else {
			$errors = $validation->stackErrorMessages($errors);
    }
  }
}

$groupData = fetchAllGroups(); //Retrieve list of all permission levels
$count = 0;
// dump($groupData);
// echo $groupData[0]->name;
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


			<form name='adminGroups' action='admin_groups.php' method='post'>
            <div>
            <h2> Administrate Groups </h2>
            <div class="well">
			  <h4>Create a new Group</h4>
				<label>Group Name:</label>
				<input type='text' name='name' />
				<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('name') ?>"></span>
            </div>

			  <br>
			  <table class='table table-hover table-list-search'>
				<tr>
				  <th>Delete</th><th>Group Name</th>
				</tr>

				<?php
				//List each permission level
				foreach ($groupData as $v1) {
				  ?>
				  <tr>
					<td><input type='checkbox' name='delete[<?=$groupData[$count]->id?>]' id='delete[<?=$groupData[$count]->id?>]' value='<?=$groupData[$count]->id?>'></td>
					<td><a href='admin_group.php?id=<?=$groupData[$count]->id?>'><?=$groupData[$count]->name?></a></td>
				  </tr>
				  <?php
				  $count++;
				}
				?>
			  </table>
			  <input type="hidden" name="csrf" value="<?=Token::generate();?>" >
			  <input class='btn btn-primary' type='submit' name='Submit' value='Save Changes' /><br><br>
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
