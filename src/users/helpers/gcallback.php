<?php
/*
 * Copyright 2011 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
require_once '../init.php';

if($user->isLoggedIn()){
	die('User is logged in, not sure how you got here.');
}

$gClient = new Google_Client();
$gClient->setClientId($cfg->get('gid'));
$gClient->setClientSecret($cfg->get('gsecret'));
$gClient->setRedirectUri($cfg->get('gcallback'));
$gClient->setScopes(array('profile'));

/*
Prepare for API calls to the Oauth2 service
*/
$gOauth2=new Google_Service_Oauth2($gClient);

/************************************************
  If we have a code back from the OAuth 2.0 flow,
  we need to exchange that with the authenticate()
  function. We store the resultant access token
  bundle in the session, and redirect to ourself.
 ************************************************/
if (isset($_GET['code'])) {
  $gClient->authenticate($_GET['code']);
  $_SESSION['access_token'] = $gClient->getAccessToken();
  Redirect::to($cfg->get('gcallback'));
}

/************************************************
  If we have an access token, we can make
  requests, else we generate an authentication URL.
 ************************************************/
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $gClient->setAccessToken($_SESSION['access_token']);
}

/************************************************
  If we're signed in we can go ahead and retrieve
  the ID token, which is part of the bundle of
  data that is exchange in the authenticate step
  - we only need to do a network call if we have
  to retrieve the Google certificate to verify it,
  and that can be cached.
 ************************************************/
if ($gClient->getAccessToken()) {
  $_SESSION['access_token'] = $gClient->getAccessToken();
	$_SESSION['gProfileData'] = $gOauth2->userinfo->get();
}

if (isset($_SESSION['gProfileData'])){
	/*
	Check for existing user account using email. If exists, then update and complete login.
	If it does not exist, then create account and complete login
	*/
	$gProfileData=$_SESSION['gProfileData'];
	//dump($gProfileData);
	$findExisting=$db->query("SELECT * FROM users WHERE email = ?",array($gProfileData['email']));
	if($findExisting->count()==1){
		/*
		Email address exists, therefore update user fields, then log them in
		*/
		$existingUser=$findExisting->first();
		$user = new User();
		/*
		Update necessary fields, like first and last names, etc.
		*/
		$fields=array('fname'=>$gProfileData['givenName'],'lname'=>$gProfileData['familyName'],'last_login'=>date("Y-m-d H:i:s"),'google_uid'=>$gProfileData['id']);
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
		Redirect::to(US_URL_ROOT.$cfg->get('redirect_login'));
		
	}else{
		/*
		Email address does not exist, therefore create new user, and log user in
		*/
		$user = new User();
		$vericode = rand(100000,999999);
		$join_date = date("Y-m-d H:i:s");
		try{
			$user->create(array(
				'username' => $gProfileData['email'],
				'fname' => $gProfileData['givenName'],
				'lname' => $gProfileData['familyName'],
				'email' => $gProfileData['email'],
				'password' =>password_hash(Token::generate(),PASSWORD_BCRYPT,array('cost' => 12)),
				'permissions' => 1,
				'account_owner' => 1,
				'stripe_cust_id' => '',
				'join_date' => $join_date,
				'company' => '',
				'email_verified' => 1,
				'active' => 1,
				'vericode' => $vericode,
				'google_uid' => $gProfileData['id'],
				));
		}catch (Exception $e) {
			die($e->getMessage());
		}
		/*
		Get user that was just created
		*/
		$findExisting=$db->query("SELECT * FROM users WHERE email = ?",array($gProfileData['email']));
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
		Redirect::to(US_URL_ROOT.$cfg->get('redirect_login'));		
	}
}elseif(Input::get('error')=='access_denied'){
	/*
	Redirect the user to oauth_denied.php pages
	*/
	Redirect::to(US_URL_ROOT.'users/oauth_denied.php');
}else{
	/*
	Google login failed somewhere and gProfileData was not set...therefore reset any $_SESSION variables, and redirect to error page
	*/
	die('Google login failed for unknown reason');
}