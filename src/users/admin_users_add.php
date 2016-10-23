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

if(isset($_POST['submit'])){
	if (Input::get('method')=='form'){
		/*
		Adding user using the form data
		*/
		$username = Input::get('username');
		$fname = Input::get('fname');
		$lname = Input::get('lname');
		$email = Input::get('email');
		
		$validation = new Validate();
		$validation->check($_POST,array(
			'username' => array('display' => 'Username','required' => true,'min' => 5,'max' => 35,'unique' => 'users',),
			'fname' => array('display' => 'First Name','required' => true,'min' => 2,'max' => 35,),
			'lname' => array('display' => 'Last Name','required' => true,'min' => 2,'max' => 35,),
			'email' => array('display' => 'Email','required' => true,'valid_email' => true,'unique' => 'users',),
			'password' => array('display' => 'Password','required' => true,'min' => 6,'max' => 25,),
			'confirm' => array('display' => 'Confirm Password','required' => true,'matches' => 'password',),
			));
		if($validation->passed()) {
			$user = new User();
			$vericode = rand(100000,999999);
			$join_date = date("Y-m-d H:i:s");
			$form_valid=TRUE;
			try {
				// echo "Trying to create user";
				
				$user->create(array(
					'username' => Input::get('username'),
					'fname' => Input::get('fname'),
					'lname' => Input::get('lname'),
					'email' => Input::get('email'),
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
				
				$successes[]='Account created successfully';
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
		}else{
			/*
			Append validation errors to error array
			*/
			foreach ($validation->errors() as $error) {
				$errors[]=$error;
			}	
		}
	}elseif(Input::get('method')=='file'){
		
		$target_dir = ABS_US_ROOT.US_URL_ROOT."users/uploads/";
		
		if(basename($_FILES["fileselect"]["name"])=='users.csv'){
			$target_file = $target_dir . basename($_FILES["fileselect"]["name"]);
			move_uploaded_file($_FILES["fileselect"]["tmp_name"], $target_file);
			$usersArray=csv_to_array($target_file, $delimiter=',');
			//dump($usersArray);
			
			foreach($usersArray as $newUser){
				$username = $newUser['username'];
				$fname = $newUser['fname'];
				$lname = $newUser['lname'];
				$email = $newUser['email'];
				$password = $newUser['password'];
				$confirm = $newUser['password'];
				
				/*
				Changed the validation source from $_POST to $newUser
				*/
				$validation = new Validate();
				$validation->check($newUser,array(
					'username' => array('display' => 'Username','required' => true,'min' => 5,'max' => 35,'unique' => 'users',),
					'fname' => array('display' => 'First Name','required' => true,'min' => 2,'max' => 35,),
					'lname' => array('display' => 'Last Name','required' => true,'min' => 2,'max' => 35,),
					'email' => array('display' => 'Email','required' => true,'valid_email' => true,'unique' => 'users',),
					'password' => array('display' => 'Password','required' => true,'min' => 6,'max' => 25,),
					));
				if($validation->passed()) {
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
				}else{
					/*
					Append validation errors to error array
					*/
					foreach ($validation->errors() as $error) {
						$errors[]=$error;
					}	
				}
			}

		}else{
			$errors[]='Uploaded filename is not correct. Expecting "users.csv" with 5 columns.';
		}
		/*
		Delete the file which was uploaded regardless of success or failure
		*/
		unlink($target_file);
		/*
		Code goes here for the logic to create users from a CSV file
		*/
		$successes[]='The "file" method is currently experimental';
	}else{
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
		<input   type="file" name="fileselect" id="fileselect">
	</div>

	<div class="form-group">
		<label for="username">Username</label>
		<input  class="form-control" type="text" name="username" placeholder="Username" value="<?=$username;?>"  autofocus>
		<p class="help-block">No Spaces or Special Characters - Min 5 characters</p>
	</div>
	<div class="form-group">
		<label for="fname">First Name</label>
		<input type="text" class="form-control" name="fname" placeholder="First Name" value="<?=$fname;?>" >
	</div>
	<div class="form-group">
		<label for="lname">Last Name</label>
		<input type="text" class="form-control" name="lname" placeholder="Last Name" value="<?=$lname;?>" >
	</div>
	<div class="form-group">
		<label for="email">Email Address</label>
		<input  class="form-control" type="text" name="email" placeholder="Email Address" value="<?=$email;?>"  >
	</div>
	<div class="form-group">
		<label for="password">Choose a Password</label>
		<input  class="form-control" type="password" name="password" placeholder="Password"  aria-describedby="passwordhelp">
		<span class="help-block" id="passwordhelp">Must be at least 6 characters</span>
	</div>
	<div class="form-group">
		<label for="confirm">Confirm Password</label>
		<input  type="password" name="confirm" class="form-control" placeholder="Confirm Password"  >
	</div>

	<input type="hidden" value="<?=Token::generate();?>" name="csrf">
	<div class="text-center"><button class="btn btn-primary" type="submit" name="submit"><span class="fa fa-plus-square"></span> Create User(s)</button></div>
</form>

</div>

</div>




	<!-- End of main content section -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
