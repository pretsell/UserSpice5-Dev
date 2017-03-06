<?php
/*
fbcallback.php
*/
require_once '../init.php';

$fb = new Facebook\Facebook([
  'app_id' => configGet('fbid'), // Replace {app-id} with your app id
  'app_secret' => configGet('fbsecret'),
  'default_graph_version' => 'v2.2',
  ]);

$helper = $fb->getRedirectLoginHelper();

try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (! isset($accessToken)) {
  if ($helper->getError()) {
    header('HTTP/1.0 401 Unauthorized');
    echo "Error: " . $helper->getError() . "\n";
    echo "Error Code: " . $helper->getErrorCode() . "\n";
    echo "Error Reason: " . $helper->getErrorReason() . "\n";
    echo "Error Description: " . $helper->getErrorDescription() . "\n";
  } else {
    header('HTTP/1.0 400 Bad Request');
    echo 'Bad request';
  }
  exit;
}

// Logged in
//echo '<h3>Access Token</h3>';
//var_dump($accessToken->getValue());

// The OAuth 2.0 client handler helps us manage access tokens
$oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
$tokenMetadata = $oAuth2Client->debugToken($accessToken);
//echo '<h3>Metadata</h3>';
//echo '<pre>';
//var_dump($tokenMetadata);
//echo '</pre>';
// Validation (these will throw FacebookSDKException's when they fail)
$tokenMetadata->validateAppId(configGet('fbid')); // Replace {app-id} with your app id
// If you know the user ID this access token belongs to, you can validate it here
//$tokenMetadata->validateUserId('123');
$tokenMetadata->validateExpiration();

if (! $accessToken->isLongLived()) {
  // Exchanges a short-lived access token for a long-lived one
  try {
    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
  } catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
    exit;
  }

  //echo '<h3>Long-lived</h3>';
  //var_dump($accessToken->getValue());
}

$_SESSION['fb_access_token'] = (string) $accessToken;

$fbUserProfile=$fb->get('/me?fields=id,first_name,last_name,email', $_SESSION['fb_access_token']);

$_SESSION['fbProfileData'] = $fbUserProfile->getGraphUser();

//dump($_SESSION);

if (isset($_SESSION['fbProfileData'])){
	/*
	Check for existing user account using email. If exists, then update and complete login.
	If it does not exist, then create account and complete login
	*/
	$fbProfileData=$_SESSION['fbProfileData'];
	//dump($fbProfileData);
	$findExisting=$db->query("SELECT * FROM users WHERE email = ?",array($fbProfileData['email']));
	if($findExisting->count()==1){
		/*
		Email address exists, therefore update user fields, then log them in
		*/
		$existingUser=$findExisting->first();
		$user = new User();
		/*
		Update necessary fields, like first and last names, etc.
		*/
		$fields=array('fname'=>$fbProfileData['first_name'],'lname'=>$fbProfileData['last_name'],'last_login'=>date("Y-m-d H:i:s"),'facebook_uid'=>$fbProfileData['id']);
		$db->update('users',$existingUser->id,$fields);

		/*
		Increment login count by 1
		*/
		$db->query("UPDATE users SET logins = logins + 1 WHERE id = ?",[$existingUser->id]);

		/*
		Log user in
		*/
		$_SESSION["user"] = $existingUser->id;

		/*
		Redirect after login
		*/
        $login_response = new StateResponse_Login;
        $login_response->respond();

	}else{
		/*
		Email address does not exist, therefore create new user, and log user in
		*/
		$user = new User();
		$vericode = rand(100000,999999);
		$join_date = date("Y-m-d H:i:s");
		try{
			$user->create(array(
				'username' => $fbProfileData['email'],
				'fname' => $fbProfileData['first_name'],
				'lname' => $fbProfileData['last_name'],
				'email' => $fbProfileData['email'],
				'password' =>password_hash(Token::generate(),PASSWORD_BCRYPT,array('cost' => 12)),
				'permissions' => 1,
				'account_owner' => 1,
				'stripe_cust_id' => '',
				'join_date' => $join_date,
				'company' => '',
				'email_verified' => 1,
				'active' => 1,
				'vericode' => $vericode,
				'facebook_uid' => $fbProfileData['id'],
				));
		}catch (Exception $e) {
			die($e->getMessage());
		}
		/*
		Get user that was just created
		*/
		$findExisting=$db->query("SELECT * FROM users WHERE email = ?",array($fbProfileData['email']));
		$existingUser=$findExisting->first();

		/*
		Increment login count by 1
		*/
		$db->query("UPDATE users SET logins = logins + 1 WHERE id = ?",[$existingUser->id]);

		/*
		Log user in
		*/
		$_SESSION["user"] = $existingUser->id;

		/*
		Redirect after login
		*/
        $login_response = new StateResponse_Login;
        $login_response->respond();
	}
}elseif(Input::get('error')=='access_denied'){
	/*
	Redirect the user to oauth_denied.php pages
	Facebook never gets here because denied is caught elsewhere.
	*/
	//Redirect::to(US_URL_ROOT.'users/oauth_denied.php');
}else{
	/*
	Facebook login failed somewhere and fbProfileData was not set...therefore reset any $_SESSION variables, and redirect to error page
	*/
	die('Facebook login failed for unknown reason');
}
