<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])) {die();}
checkToken();

$errors = $successes = [];

if ($user->isLoggedIn()) {
	$userId = $user->data()->id;
} else {
	$userId = 0;
}

$id = Input::get('id');
if ($id==$user->data()->id || $id == null) {
	/*
	Displaying all user data and update form
	*/
	$displayFullProfile=TRUE;
	$userData = $user->data();
} else {
	/*
	Displaying only public user data without form controls
	*/
	$displayFullProfile=FALSE;
	$userQ = $db->query("SELECT * FROM users WHERE id = ?",array($id));
	$userData = $userQ->first();
}
if (!$id)
	$id = $userId;

/*
Process form data if submitted
*/

$validation = new Validate([
	'username'=>['action'=>'update', 'update_id'=>$id],
	'fname',
	'lname',
	'email'=>['action'=>'update', 'update_id'=>$id],
	'old' => array(
	  'display' => 'Old Password',
	  'required' => false, // only required if changing pass
	),
	'password' => ['required'=>false], // only required if changing pass
	'confirm' => ['required'=>false], // only required if changing pass
	'bio',
]);
$fldList = [ 'username', 'fname', 'lname', 'email', 'timezone_string', 'bio' ];
if (Input::exists()) {
	foreach ($fldList as $fn) {
		$$fn = $fields[$fn] = Input::get($fn);
	}
	$validation->check($_POST);
	if ($validation->passed()) {
		if ($email != $userData->email && $userData->email_act=1) { // changed email
			$fields['email_verified']=0; // no longer verified
		}
		if (!$site_settings->allow_username_change)
			unset($fields['username']);
		$db->update('users',$userId,$fields);
		$successes[]=lang('ACCOUNT_DETAILS_UPDATED');
	} else {
		$errors = $validation->stackErrorMessages($errors);
	}

	/*
	Update password
	*/
	if (Input::get('password')!=="") {
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
		if ($validation->passed()) {
		  if (!password_verify(Input::get('old'),$user->data()->password)) {
				$errors[]=lang('ACCOUNT_PASSWORD_INVALID');
		  } else {
				$new_password_hash = password_hash(Input::get('password'),PASSWORD_BCRYPT,array('cost' => 12));
				$user->update(array('password' => $new_password_hash,),$user->data()->id);
				$successes[]='Password updated.';
			}
		} else {
			$errors = stackErrorMessages($errors);
		}
	}
} else {
	foreach ($fldList as $f) {
		$$f = $userData->$f;
		#echo "DEBUG: f=$f, \$\$f=".$$f."<br />\n";
	}
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
<div class="form-group">
	<label>Member Since</label>
	<input  class='form-control' type='text' name='signupdate' value='<?=$signupDate?>' readonly/>
</div>

<div class="form-group">
	<label>Number of Logins</label>
	<input  class='form-control' type='text' name='logins' value='<?=$userData->logins?>' readonly/>
</div>

</div>
<div class="col-xs-12 col-md-9">
<?php
if ($displayFullProfile) {
?>
	<h1><?=$userData->username?>'s Settings</h1>

	<strong>Want to change your profile picture? </strong><br>
	Visit <a href="https://en.gravatar.com/">https://en.gravatar.com/</a> and setup an account with the email address <?=$email?>.
	It works across millions of sites. It's fast and easy!<br>

	<?=resultBlock($errors,$successes);?>

	<form name='updateAccount' action='profile.php' method='post'>

		<div class="form-group">
			<label>Username</label>
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('username') ?>"></span>
			<input  class='form-control' type='text' name='username' value='<?=$username?>' <?php if (!$site_settings->allow_username_change) echo "readonly"; ?>/>
		</div>

		<div class="form-group">
			<label>First Name</label>
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('fname') ?>"></span>
			<input  class='form-control' type='text' name='fname' value='<?=$fname?>' />
		</div>

		<div class="form-group">
			<label>Last Name</label>
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('lname') ?>"></span>
			<input  class='form-control' type='text' name='lname' value='<?=$lname?>' />
		</div>

		<div class="form-group">
			<label>Email</label>
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('email') ?>"></span>
			<input class='form-control' type='text' name='email' value='<?=$email?>' />
		</div>

		<div class="form-group">
			<label>Old Password (required to change password)</label>
			<input class='form-control' type='password' name='old' />
		</div>

		<div class="form-group">
			<label>New Password</label>
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('password') ?>"></span>
			<input class='form-control' type='password' name='password' />
		</div>

		<div class="form-group">
			<label>Confirm Password</label>
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('confirm') ?>"></span>
			<input class='form-control' type='password' name='confirm' />
		</div>

		<div class="form-group">
			<label>Timezone</label>
			<?=timezone_dropdown($timezone_string); //function from helpers.php?>
		</div>

		<div class="form-group">
			<label>Biography</label>
			<span class="glyphicon glyphicon-info-sign" title="<?= $validation->describe('bio') ?>"></span>
			<textarea rows="10" id="biographyText" name="bio" ><?=$bio;?></textarea></p>
		</div>

		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<p class="text-center"><input class='btn btn-primary' type='submit' value='Update' class='submit' />
		<a class="btn btn-info" href="profile.php">Cancel</a></p>

	</form>

<?php
} else {
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

<!-- If you disable this script below you will get a standard textarea with NO WYSIWYG editor. That simple -->
<script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
<script>
tinymce.init({
  selector: '#biographyText'
});
</script>
