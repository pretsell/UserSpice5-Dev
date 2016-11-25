<?php
require_once '../init.php';
$loginstatus=Input::get('loginstatus');
if(!$user->isLoggedIn() && $loginstatus!='Not Logged In'){
	echo US_URL_ROOT.'index.php';	
}else{
	echo '0';	
}
?>