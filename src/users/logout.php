<?php

require_once 'init.php';

if(file_exists(US_DOC_ROOT.US_URL_ROOT.'usersc/scripts/just_before_logout.php')){
	require_once US_DOC_ROOT.US_URL_ROOT.'usersc/scripts/just_before_logout.php';
}else{
	//Feel free to change where the user goes after logout!
}
$user->logout();
if(file_exists(US_DOC_ROOT.US_URL_ROOT.'usersc/scripts/just_after_logout.php')){
	require_once US_DOC_ROOT.US_URL_ROOT.'usersc/scripts/just_after_logout.php';
}else{
	//Feel free to change where the user goes after logout!
	Redirect::to(US_URL_ROOT.configGet('redirect_logout'));
}
?>
