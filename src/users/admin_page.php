<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';


# Secures the page...required for page permission management
if (!securePage($_SERVER['PHP_SELF'])) { die(); }
checkToken();

//PHP Goes Here!
$pageId = Input::get('id');
$errors = array();
$successes = array();

//Check if selected pages exist
if(!pageIdExists($pageId)) {
  Redirect::to("admin_pages.php"); die();
}

$pageDetails = fetchPageDetails($pageId); //Fetch information specific to page

//Forms posted
if(Input::exists()) {

	$update = 0;

	if(!empty($_POST['private'])) {
		$private = Input::get('private');
	}

	//Toggle private page setting
	if (isset($private) AND $private == 'Yes') {
		if ($pageDetails->private == 0) {
			if (updatePrivate($pageId, 1)) {
				$successes[] = "Private access has been toggled";
			} else {
				$errors[] ="SQL error";
			}
		}
	} elseif ($pageDetails->private == 1) {
		if (updatePrivate($pageId, 0)) {
			$successes[] = "Private access has been toggled";
		} else {
			$errors[] ="SQL error";
		}
	}

	//Remove group's access to page
	if(!empty($_POST['removeGroup'])) {
		$remove = $_POST['removeGroup'];
		if ($deletion_count = deleteGroupsPages($pageId, $remove)) {
			$successes[] = "Page access removed";
		} else {
			$errors[] = "SQL error";
		}
	}

	//give group access to page
	if(!empty($_POST['addGroup'])) {
		$add = $_POST['addGroup'];
		$addition_count = 0;
		foreach($add as $groupId) {
			if(addGroupsPages($pageId, $groupId)) {
				$addition_count++;
			}
		}
		if ($addition_count > 0 ) {
			$successes[] = "Page access added";
		}
	}
	$pageDetails = fetchPageDetails($pageId);
}
$pageGroups = fetchGroupsByPage($pageId); // groups with auth to access page
$groupData = fetchAllGroups();
?>
    <!-- Page Heading -->
    <div class="row">
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
        <!-- Main Center Column -->
        <div class="col-xs-12">
          <!-- Content Goes Here. Class width can be adjusted -->

			<h2>Page Authorizations </h2>
			<?php
			echo display_errors($errors);
			echo display_successes($successes);
			?>

			<form name='adminPage' action='admin_page.php?id=<?=$pageId;?>' method='post'>
				<input type='hidden' name='process' value='1'>

			<div class="row">
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Information</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<label>ID:</label>
						<?= $pageDetails->id; ?>
						</div>
						<div class="form-group">
						<label>Name:</label>
						<?= $pageDetails->page; ?>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->

			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Public or Private?</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<label>Private:</label>
						<?php
						$checked = ($pageDetails->private == 1)? ' checked' : ''; ?>
						<input type='checkbox' name='private' id='private' value='Yes'<?=$checked;?>>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->

			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Remove Access</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<?php
						//Display list of groups with access
						$groupIds = [];
						foreach($pageGroups as $group) {
							$groupIds[] = $group->group_id;
						}
						foreach ($groupData as $v1) {
							if (in_array($v1->id,$groupIds)) { ?>
							<input type='checkbox' name='removeGroup[]' id='removeGroup[]' value='<?=$v1->id;?>'> <?=$v1->name;?><br/>
							<?php }} ?>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->

			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Add Access</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<?php
						//Display list of groups without access
						foreach ($groupData as $v1) {
						if(!in_array($v1->id,$groupIds)) { ?>
						<input type='checkbox' name='addGroup[]' id='addGroup[]' value='<?=$v1->id;?>'> <?=$v1->name;?><br/>
						<?php }} ?>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->
			</div><!-- /.row -->

			<input type="hidden" name="csrf" value="<?=Token::generate();?>" >
			<p><input class='btn btn-primary' type='submit' value='Update' class='submit' /></p>
			</form>
        </div>
    </div>

    <!-- /.row -->
    <!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
