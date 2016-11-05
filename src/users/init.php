<?php

error_reporting(E_ALL);
ini_set("display_errors",1);

$abs_us_root=$_SERVER['DOCUMENT_ROOT'];

$self_path=explode("/", $_SERVER['PHP_SELF']);
$self_path_length=count($self_path);
$file_found=FALSE;

for($i = 1; $i < $self_path_length; $i++){
	array_splice($self_path, $self_path_length-$i, $i);
	$us_url_root=implode("/",$self_path)."/";

	if (file_exists($abs_us_root.$us_url_root.'z_us_root.php')){
		$file_found=TRUE;
		break;
	}else{
		$file_found=FALSE;
	}
}
/*
ABS_US_ROOT contains the absolute file system path to the webserver root
-For example: /home/username/www
US_URL_ROOT contains any directories between ABS_US_ROOT and the UserSpice installation location
-For example: /us5/

To require a file located in the UserSpice folder users/ you would write:
require ABS_US_ROOT.US_URL_ROOT.'users/filename.php';
*/

define("ABS_US_ROOT",$abs_us_root);
define("US_URL_ROOT",$us_url_root);

/*
require_once classes from users/classes/*.php
using PHP autoloader conflicts with Google and Facebook autoloader
*/
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/Config.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/Cookie.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/DB.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/Form.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/FormField.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/Hash.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/Input.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/Redirect.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/Session.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/Token.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/User.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/Validate.php';

/*
Autoload Facebook and Google APIs/SDKs
*/
require_once ABS_US_ROOT.US_URL_ROOT.'users/social_src/Google/autoload.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/social_src/Facebook/autoload.php';

/*
session_start() is placed after the autoloading so that class definitions are loaded before any session processing
*/
session_start();

require ABS_US_ROOT.US_URL_ROOT.'users/db_cred.php';

/*
Global Config Variables
*/
$GLOBALS['config'] = array(
'mysql'      => array(
'host'         => $dbHost,
'username'     => $dbUsername,
'password'     => $dbPassword,
'db'           => $dbDatabase,
'options'			 => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode = ''",
												PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING),
),
'remember'        => array(
  'cookie_name'   => 'pmqesoxiw318374csb',
  'cookie_expiry' => 604800  //One week, feel free to make it longer
),
'session' => array(
  'session_name' => 'user',
  'token_name' => 'token',
)
);
$GLOBALS['cfg'] = new Config();

/*
$us_tables = the tables that makeup userspice
*/
$us_tables=['menus','pages','permissions','permission_page_matches','settings','users','users_online','users_session','user_permission_matches','users_test'];

/*
Include other class defintions
*/
require_once ABS_US_ROOT.US_URL_ROOT.'users/classes/phpmailer/PHPMailerAutoload.php';

/*
Include necessary header files
*/
require_once ABS_US_ROOT.US_URL_ROOT.'users/helpers/helpers.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/helpers/menus.php';
/*
Get DB instance for page
*/
$db = DB::getInstance();

/*
Look for "remember me" cookie
*/
if(Cookie::exists(configGet('remember/cookie_name')) && !Session::exists(configGet('session/session_name'))){
	$hash = Cookie::get(configGet('remember/cookie_name'));
	$hashCheck = $db->query("SELECT * FROM users_session WHERE hash = ? AND uagent = ?",array($hash,Session::uagent_no_version()));

	if ($hashCheck->count()) {
		$user = new User($hashCheck->first()->user_id);
		$user->login();
	}
}

/*
Instantiate user class
*/
$user = new User();

if($user->isLoggedIn()){
	/*
	Check if user is blocked
	*/
	if ($user->data()->permissions==0){
		$user_blocked=true;
	}

	/*
	Set user time zone
	php.net/manual/en/timezones.php
	*/
	if($user->data()->timezone_string !=null){
		date_default_timezone_set($user->data()->timezone_string);
	}
	$user_id=$user->data()->id;
}else{
	if(configGet('track_guest') == 1){
		$user_id=0;
	}
}
/*
Process Track Guest
*/
new_user_online($user_id);

/*
If user logged in and not verified, then redirect
*/
if($user->isLoggedIn()){
	if($user->data()->email_verified == 0 && $currentPage != 'verify.php' && $currentPage != 'logout.php' && $currentPage != 'verify_thankyou.php'){
		$user->logout();
		Redirect::to(US_URL_ROOT.'users/verify_resend.php');
	}
}

/*
Maintenance Mode
*/
if (configGet('site_offline')==1){
	die("The site is currently offline.");
}

/*
SSL Enforcement
*/
if (configGet('force_ssl') == 1){
	if (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS']) {
		// if request is not secure, redirect to secure url
		$url = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		Redirect::to($url);
		exit;
	}
}

/*
Session Time Out Management
*/
//if (isset($_SESSION['LAST_ACTIVITY']) && ((time()-$_SESSION['LAST_ACTIVITY']) > configGet('session_timeout')) && $user->isLoggedIn()) {
if (isset($_SESSION['LAST_ACTIVITY']) && ((time()-$_SESSION['LAST_ACTIVITY']) > configGet('session_timeout'))) {
	session_unset(); // unset $_SESSION variable for the run-time
	session_destroy(); // destroy session data in storage

	//Redirect::to(US_URL_ROOT);
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

ob_start();
