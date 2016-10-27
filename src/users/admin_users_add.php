<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';


/*
Secures the page...required for page group/permission management
*/
if (!securePage($_SERVER['PHP_SELF'])) {die();}
checkToken();

$errors = $successes = [];
$val_err = null;
$username = null;
$fname = null;
$lname = null;
$email = null;
$form_valid=FALSE;
$groupOpsQ = $db->query("SELECT * FROM groups");
$groupOps = $groupOpsQ->results();
$validation = new Validate([
	'username' => ['action'=>'add'],
	'fname',
	'lname',
	'email' => ['action'=>'add'],
	'password',
	'confirm',
]);

if (isset($_POST['submit'])) {
	if (Input::get('method')=='form') {
		#Adding user using the form data
		$username = Input::get('username', 'post');
		$fname = Input::get('fname', 'post');
		$lname = Input::get('lname', 'post');
		$email = Input::get('email', 'post');

		# $validation was set above...
		$validation->check($_POST);
		if ($validation->passed()) {
			$user = new User();
			$vericode = rand(100000,999999);
			$join_date = date("Y-m-d H:i:s");
			$form_valid=TRUE;
			try {
				// echo "Trying to create user";

				$user->create(array(
					'username' => $username,
					'fname' => $fname,
					'lname' => $lname,
					'email' => $email,
					'password' =>password_hash(Input::get('password'), PASSWORD_BCRYPT, array('cost' => 12)),
					'permissions' => 1,
					'account_owner' => 1,
					'stripe_cust_id' => '',
					'join_date' => $join_date,
					'company' => '',
					'email_verified' => 0,
					'active' => 1,
					'vericode' => $vericode,
					));

				$successes[]=lang('ACCOUNT_USER_ADDED', $username);

				#empty form variables so they aren't displayed again
				$username = null;
				$fname = null;
				$lname = null;
				$email = null;
			} catch (Exception $e) {
				die($e->getMessage());
			}
		} else { // didn't pass validation
			$errors = $validation->stackErrorMessages($errors);
		}
	} elseif (Input::get('method')=='file') {

		$target_dir = ABS_US_ROOT.US_URL_ROOT."users/uploads/";

		if (basename($_FILES["fileselect"]["name"])=='users.csv') {
			$target_file = $target_dir . basename($_FILES["fileselect"]["name"]);
			move_uploaded_file($_FILES["fileselect"]["tmp_name"], $target_file);
			$usersArray=csv_to_array($target_file, $delimiter=',');
			//dump($usersArray);

			foreach($usersArray as $newUser) {
				$username = $newUser['username'];
				$fname = $newUser['fname'];
				$lname = $newUser['lname'];
				$email = $newUser['email'];
				$password = $newUser['password'];
				$confirm = $newUser['password'];

				$validation = new Validate([
					'username' => ['action'=>'add'],
					'fname',
					'lname',
					'email' => ['action'=>'add'],
					'password',
				]);
				# Validate from $newUser source instead of $_POST
				$validation->check($newUser);
				if ($validation->passed()) {
					$user = new User();
					$vericode = rand(100000,999999);
					$join_date = date("Y-m-d H:i:s");
					$form_valid=TRUE;
					try {
						// echo "Trying to create user";

						$user->create(array(
							'username' => $newUser['username'],
							'fname' => $newUser['fname'],
							'lname' => $newUser['lname'],
							'email' => $newUser['email'],
							'password' =>password_hash($newUser['password'], PASSWORD_BCRYPT, array('cost' => 12)),
							'permissions' => 1,
							'account_owner' => 1,
							'stripe_cust_id' => '',
							'join_date' => $join_date,
							'company' => '',
							'email_verified' => 0,
							'active' => 1,
							'vericode' => $vericode,
							));

						$successes[]='Account "'.$newUser['username'].'" created successfully';
						/*
						empty form variables so they aren't displayed again
						*/
						$username = null;
						$fname = null;
						$lname = null;
						$email = null;

					} catch (Exception $e) {
						die($e->getMessage());
					}
				} else {
					$errors = $validation->stackErrorMessages($errors);
				}
			}
		} else {
			$errors[]='Uploaded filename is not correct. Expecting "users.csv" with 5 columns.';
		}

		#Delete the file which was uploaded regardless of success or failure
		unlink($target_file);
		/*
		Code goes here for the logic to create users from a CSV file
		*/
		$successes[]='The "file" method is currently experimental';
	} else {
		/*
		Do nothing since not valid method
		*/
		$errors[]='Invalid user creation method';
	}

}
?>
<div class="row">
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>

	<div class="col-xs-12">
	<h2>Add User(s)</h2>
	</div>
</div>

<div class="row">
<div class="col-xs-12">
<?php
echo display_errors($errors);
echo display_successes($successes);
?>
</div>
<div class="col-xs-12">
<form action="admin_users_add.php" method="post" enctype="multipart/form-data">
	<div class="form-group">
		<label for="method">Method</label><br/>
		<label class="radio-inline"><input type="radio" name="method" value="form" checked>Form (below)</label>
		<label class="radio-inline"><input type="radio" name="method" value="file">File (please select a file) - NOT implemented yet</label>
	</div>
	<div class="form-group">
		<label for="fileselect">Select File</label><br/>
		<input type="file" name="fileselect" id="fileselect">
	</div>

	<div class="form-group">
		<label for="username">Username</label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('username') ?>"></span>
		<input class="form-control" type="text" name="username" placeholder="Username" value="<?=$username;?>" autofocus>
	</div>
	<div class="form-group">
		<label for="fname">First Name</label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('fname') ?>"></span>
		<input type="text" class="form-control" name="fname" placeholder="First Name" value="<?=$fname;?>" >
	</div>
	<div class="form-group">
		<label for="lname">Last Name</label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('lname') ?>"></span>
		<input type="text" class="form-control" name="lname" placeholder="Last Name" value="<?=$lname;?>" >
	</div>
	<div class="form-group">
		<label for="email">Email Address</label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('email') ?>"></span>
		<input  class="form-control" type="text" name="email" placeholder="Email Address" value="<?=$email;?>"  >
	</div>
	<div class="form-group">
		<label for="password">Choose a Password</label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('password') ?>"></span>
		<input  class="form-control" type="password" name="password" placeholder="Password"  aria-describedby="passwordhelp">
	</div>
	<div class="form-group">
		<label for="confirm">Confirm Password</label>
		<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('confirm') ?>"></span>
		<input  type="password" name="confirm" class="form-control" placeholder="Confirm Password" aria-describedby="confirmhelp" >
	</div>

	<input type="hidden" value="<?=Token::generate();?>" name="csrf">
	<div class="text-center"><button class="btn btn-primary" type="submit" name="submit"><span class="fa fa-plus-square"></span> Create User(s)</button></div>
</form>

</div>

</div>




	<!-- End of main content section -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
