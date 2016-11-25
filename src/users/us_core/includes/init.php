<?php
/*
UserSpice
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/*
 * us_core/includes/init.php
 *
 * Provides the initialization needed for all UserSpice forms and pages.
 *
 * DO NOT CHANGE THIS FILE. Instead, copy this file to local/includes/init.php
 * and make any needed changes there.
 *
 * SO IF I DON'T CHANGE INIT.PHP, HOW DO I CONFIGURE USERSPICE?!
 * If you are accustomed to making configuration changes in init.php (from
 * version 4 and before) then please note that a lot of configuration has been
 * moved into the database (see DB table `settings`) and can be modified through
 * the "settings" menu on the admin dashboard (admin_redirects.php,
 * admin_general.php, etc.). Configuration that has not moved to the database
 * is now done through editing local/config.php.
 */

/*
If the site over-rides init.php with a copy in local/includes then include
that file and don't continue here.
*/
# IF YOU COPY THIS SCRIPT TO local/includes/init.php MAKE SURE THAT YOU
# GET RID OF THESE LINES (BELOW) - otherwise it is circular
if (file_exists(US_ROOT_DIR.'local/includes/init.php')) {
    require_once US_ROOT_DIR.'local/includes/init.php';
    return;
}

error_reporting(E_ALL);
ini_set("display_errors",1);

/*
Get the values from local/config.php and make it available via the class
"Config" in Classes/Config.php (as defined (class "US_Config") in
us_core/Classes/Config.php and then implemented (class "Config") in
local/Classes/Config.php).
*/
include_once US_ROOT_DIR.'us_core/classes/Config.php';
include_once US_ROOT_DIR.'local/classes/Config.php';
include_once US_ROOT_DIR.'local/config.php';

/*
require_once classes from users/classes/*.php
using PHP autoloader conflicts with Google and Facebook autoloader
*/
spl_autoload_register('us_classloader');
function us_classloader($class_name) {
    # FormField_Text and FormField_Select and etc are contained in FormFieldTypes.php
    if (preg_match('/^FormField_/', $class_name)) {
        $class_name = 'FormFieldTypes';
    }
    include_once US_ROOT_DIR.'us_core/classes/'.$class_name . '.php';
    include_once US_ROOT_DIR.'local/classes/'.$class_name . '.php';
}

/*
Autoload Facebook and Google APIs/SDKs
*/
require_once US_ROOT_DIR.'us_core/social_src/Google/autoload.php';
require_once US_ROOT_DIR.'us_core/social_src/Facebook/autoload.php';

/*
session_start() is placed after the autoloading so that class definitions are loaded before any session processing
*/
session_start();
#var_dump($_SESSION);

require_once US_ROOT_DIR.'local/config.php';

/*
$us_tables = the tables that makeup userspice
*/
$us_tables=['field_defs', 'groups', 'groups_menus', 'groups_pages', 'groups_roles_users',
    'groups_users', 'groups_users_raw', 'grouptypes', 'menus', 'pages', 'profiles',
    'settings', 'users', 'users_online', 'users_session', ];

# Prepare $T[] for prefix operations (T=tableArray)
$prefix = configGet('mysql/prefix', '');
foreach ($us_tables as $t) {
    $T[$t] = $prefix.$t;
}

# Include other class definitions as late loaders
require_once US_ROOT_DIR.'us_core/classes/phpmailer/PHPMailerAutoload.php';

# Bring in the rest of the helpers, checking for localized versions where appropriate
require_once US_ROOT_DIR.'us_core/helpers/helpers.php';
if (file_exists($localhelp = US_ROOT_DIR.'local/helpers/helpers.php')) {
    require_once $localhelp; // site's custom functions
}
require_once US_ROOT_DIR.'us_core/helpers/us_helpers.php';
# Now that we have access to pathFinder() (defined in us_helpers.php) we can
# use it for all future include/require statements to ensure we are getting
# the localized version if it exists.
#dbg("abs=".$US_DOC_ROOT."<br />\n");
#dbg("url=".$us_url_root."<br />\n");
require_once pathFinder('helpers/users_online.php');
require_once pathFinder('helpers/menus.php');
require_once pathFinder('helpers/menus.php');
require_once pathFinder('helpers/class.treeManager.php');
require_once pathFinder('helpers/Shuttle_Dumper.php');
require_once pathFinder('helpers/utilities.php');

# Pull in language data for lang() calls
require_once pathFinder('language/language.php');

# Get an instance of the database
$db = DB::getInstance();

/*
Look for "remember me" cookie
*/
if(Cookie::exists(configGet('remember/cookie_name')) && !Session::exists(configGet('session/session_name'))){
	$hash = Cookie::get(configGet('remember/cookie_name'));
	$hashCheck = $db->query("SELECT * FROM $T[users_session] WHERE hash = ? AND uagent = ?",array($hash,Session::uagent_no_version()));

	if ($hashCheck->count()) {
		$user = new User($hashCheck->first()->user_id);
		$user->login();
	}
}

/*
Instantiate user class
*/
$user = new User();

if ($user->isLoggedIn()) {
    #dbg("User IS logged in<br />\n");
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
} else {
    #dbg("User is NOT logged in<br />\n");
	$user_id=0;
}

# Track guests
new_user_online($user_id);

/*
If user logged in and not verified, then redirect
*/
if ($user->isLoggedIn()) {
    #dbg("Init: user IS logged in - checking if emial verified<br />\n");
	if($user->data()->email_verified == 0 && $currentPage != 'verify.php' && $currentPage != 'logout.php' && $currentPage != 'verify_thankyou.php'){
		$user->logout();
		Redirect::to(US_URL_ROOT.'users/verify_resend.php');
	}
}

# Maintenance Mode?
if (configGet('site_offline')==1) {
	die("The site is currently offline.");
}

/*
SSL Enforcement
*/
if (configGet('force_ssl') == 1) {
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
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

ob_start();
