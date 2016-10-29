<?php
/*
Create Google Sign-In auth url
*/

$gClient = new Google_Client();
$gClient->setClientId($site_settings->gid);
$gClient->setClientSecret($site_settings->gsecret);
$gClient->setRedirectUri($site_settings->gcallback);
$gClient->setScopes(array('email','profile'));

$gAuthUrl = $gClient->createAuthUrl();
?>


