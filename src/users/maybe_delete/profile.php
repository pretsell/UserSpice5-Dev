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

/*
Initialize variables for the page
*/
$errors=[];
$successes=[];

if($user->isLoggedIn()) {
	$userId = $user->data()->id;
}else{
	$userId = 0;
}

/*
If $_POST data exists, then check CSRF token, and kill page if not correct...no need to process rest of page or form data
*/
if (Input::exists()) {
	if(!Token::check(Input::get('csrf'))){
		die('Token doesn\'t match!');
	}
}

if(Input::get('id')==$user->data()->id || Input::get('id') == null){
	/*
	Displaying all user data and update form
	*/
	$displayFullProfile=TRUE;
	$userData = $user->data();
}else{
	/*
	Displaying only public user data without form controls
	*/
	$displayFullProfile=FALSE;
	$userQ = $db->query("SELECT * FROM users WHERE id = ?",array(Input::get('id')));
	$userData = $userQ->first();
}

/*
Process form data if submitted
*/

if (Input::exists()){
	$validation = new Validate();

	/*
	Update username (display name)
	*/
	if ($userData->username != Input::get("username")){
		$displayname = Input::get("username");

		$fields=array('username'=>$displayname);
		$validation->check($_POST,array(
		'username' => array(
		  'display' => 'Username',
		  'required' => true,
		  'unique_update' => 'users,'.$userId,
		  'min' => 1,
		  'max' => 25
		)
		));
		if($validation->passed()){
			/*
			Username changes are disabled by commenting out this field and disabling input in the form/view;
			*/
			//$db->update('users',$userId,$fields);

			$successes[]="Username updated.";
		}else{
			/*
			Validation did not pass so copy errors from validation class to $errors[]
			*/
			foreach ($validation->errors() as $error) {
				$errors[] = $error;
			}
		}
	}else{
		$displayname=$userData->username;
	}

	/*
	Update first name
	*/
	if ($userData->fname != Input::get("fname")){
		$fname = Input::get("fname");

		$fields=array('fname'=>$fname);
		$validation->check($_POST,array(
		'fname' => array(
		  'display' => 'First Name',
		  'required' => true,
		  'min' => 1,
		  'max' => 25
		)
		));
		if($validation->passed()){
			$db->update('users',$userId,$fields);

			$successes[]='First name updated.';
		}else{
			/*
			Validation did not pass so copy errors from validation class to $errors[]
			*/
			foreach ($validation->errors() as $error) {
				$errors[] = $error;
			}

		}
	}else{
		$fname=$userData->fname;
	}

	/*
	Update last name
	*/
	if ($userData->lname != Input::get("lname")){
	  $lname = Input::get("lname");

	  $fields=array('lname'=>$lname);
	  $validation->check($_POST,array(
		'lname' => array(
		  'display' => 'Last Name',
		  'required' => true,
		  'min' => 1,
		  'max' => 25
		)
	  ));
	if($validation->passed()){
	  $db->update('users',$userId,$fields);

	  $successes[]='Last name updated.';
	}else{
			/*
			Validation did not pass so copy errors from validation class to $errors[]
			*/
			foreach ($validation->errors() as $error) {
				$errors[] = $error;
			}

	  }
	}else{
		$lname=$userData->lname;
	}

	/*
	Update email
	*/
	if ($userData->email != Input::get("email")){
	  $email = Input::get("email");
	  $fields=array('email'=>$email);
	  $validation->check($_POST,array(
		'email' => array(
		  'display' => 'Email',
		  'required' => true,
		  'valid_email' => true,
		  'unique_update' => 'users,'.$userId,
		  'min' => 3,
		  'max' => 75
		)
	  ));
	if($validation->passed()){
	  $db->update('users',$userId,$fields);
			if($emailR->email_act=1){
				$db->update('users',$userId,['email_verified'=>0]);
			}


	  $successes[]='Email updated.';
	}else{
			/*
			Validation did not pass so copy errors from validation class to $errors[]
			*/
			foreach ($validation->errors() as $error) {
				$errors[] = $error;
			}
	  }

	}else{
		$email=$userData->email;
	}
	
	/*
	Update password
	*/
	if(Input::get('password')!=="") {
	  $validation->check($_POST,array(
		'old' => array(
		  'display' => 'Old Password',
		  'required' => true,
		),
		'password' => array(
		  'display' => 'New Password',
		  'required' => true,
		  'min' => 6,
		),
		'confirm' => array(
		  'display' => 'Confirm New Password',
		  'required' => true,
		  'matches' => 'password',
		),
	  ));
	  	/*
		Validation did not pass so copy errors from validation class to $errors[]
		*/
		foreach ($validation->errors() as $error) {
			$errors[] = $error;
		}

	  if (!password_verify(Input::get('old'),$user->data()->password)) {
			foreach ($validation->errors() as $error) {
				$errors[] = $error;
			}
			$errors[]='Your password does not match our records.';
	  }
		if (empty($errors)) {
			//process
			$new_password_hash = password_hash(Input::get('password'),PASSWORD_BCRYPT,array('cost' => 12));
			$user->update(array('password' => $new_password_hash,),$user->data()->id);
			$successes[]='Password updated.';
		}
	}
	/*
	Update timezone
	*/
	if ($userData->timezone_string != Input::get('timezone_string')){
		$timezone_string = Input::get('timezone_string');
		$fields=array('timezone_string'=>$timezone_string);
		$db->update('users',$userData->id,$fields);
	}else{
		$timezone_string=$userData->timezone_string;
	}	
	
	/*
	Update biography text
	*/
	if ($userData->bio != Input::get('bio')){
		$bio = Input::get('bio');
		$fields=array('bio'=>$bio);
		$validation->check($_POST,array(
		'bio' => array(
		'display' => 'Bio',
		'required' => true
		)
		));
		if($validation->passed()){
			$db->update('users',$userData->id,$fields);
		}	
	}else{
		$bio=$userData->bio;
	}
	
	
}else{
	$displayname=$userData->username;
	$fname=$userData->fname;
	$lname=$userData->lname;
	$email=$userData->email;
	$timezone_string=$userData->timezone_string;
	$bio=$userData->bio;
}

/*
Process display variables
*/
$grav = get_gravatar(strtolower(trim($userData->email)));
$rawDate = date_parse($userData->join_date);
$signupDate = $rawDate['year']."-".$rawDate['month']."-".$rawDate['day'];
?>
<div class="row">
<div class="col-xs-12 col-md-3">
<p><img src="<?=$grav; ?>" class="img-thumbnail" alt="Generic placeholder thumbnail"></p>
</div>
<div class="col-xs-12 col-md-9">
<?php
if($displayFullProfile){
?>
	<h1><?=$userData->username?>'s Settings</h1>
	
	<?=display_errors($errors);?>
	<?=display_successes($successes);?>
	
	<form name='updateAccount' action='profile.php' method='post'>

		<div class="form-group">
			<label>Member Since</label>
			<input  class='form-control' type='text' name='signupdate' value='<?=$signupDate?>' readonly/>
		</div>

		<div class="form-group">
			<label>Number of Logins</label>
			<input  class='form-control' type='text' name='logins' value='<?=$userData->logins?>' readonly/>
		</div>
		
		<div class="form-group">
			<label>Username</label>
			<input  class='form-control' type='text' name='username' value='<?=$displayname?>' readonly/>
		</div>

		<div class="form-group">
			<label>First Name</label>
			<input  class='form-control' type='text' name='fname' value='<?=$fname?>' />
		</div>

		<div class="form-group">
			<label>Last Name</label>
			<input  class='form-control' type='text' name='lname' value='<?=$lname?>' />
		</div>

		<div class="form-group">
			<label>Email</label>
			<input class='form-control' type='text' name='email' value='<?=$email?>' />
		</div>

		<div class="form-group">
			<label>Old Password (required to change password)</label>
			<input class='form-control' type='password' name='old' />
		</div>

		<div class="form-group">
			<label>New Password (8 character minimum)</label>
			<input class='form-control' type='password' name='password' />
		</div>

		<div class="form-group">
			<label>Confirm Password</label>
			<input class='form-control' type='password' name='confirm' />
		</div>
		
		<div class="form-group">
			<label>Timezone</label>
			<?=timezone_dropdown($timezone_string); //function from helpers.php?>
		</div>		

		<div class="form-group">
			<label>Biography</label>
			<textarea rows="10" id="biographyText" name="bio" ><?=$bio;?></textarea></p>		
		</div>
		
		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<p class="text-center"><input class='btn btn-primary' type='submit' value='Update' class='submit' />
		<a class="btn btn-info" href="profile.php">Cancel</a></p>

	</form>	
	
<?php
}else{
?>
	<h1><?=$userData->username?>'s Settings</h1>
	<p><?php echo $userData->fname." ".$userData->lname?></p>
	<p><label>Member Since</label><br/><?php echo $signupDate ?></p>
	<p><label>Number of Logins</label><br/><?php echo $userData->logins ?></p>
	<p><label>Biography</label><br/><?=html_entity_decode($userData->bio)?></p>
<?php
}
?>
</div>
</div>
 
<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- If you disable this script below you will get a standard textarea with NO WYSIWYG editor. That simple -->
<script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
<script>
tinymce.init({
  selector: '#biographyText'
});
</script>

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>