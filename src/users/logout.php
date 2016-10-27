<?php

require_once 'init.php';

if(file_exists(ABS_US_ROOT.US_URL_ROOT.'usersc/scripts/just_before_logout.php')){
	require_once ABS_US_ROOT.US_URL_ROOT.'usersc/scripts/just_before_logout.php';
}else{
	//Feel free to change where the user goes after logout!
}
$user->logout();
if(file_exists(ABS_US_ROOT.US_URL_ROOT.'usersc/scripts/just_after_logout.php')){
	require_once ABS_US_ROOT.US_URL_ROOT.'usersc/scripts/just_after_logout.php';
}else{
	//Feel free to change where the user goes after logout!
	Redirect::to(US_URL_ROOT.$$cfg->get('redirect_logout'));
}
?>
