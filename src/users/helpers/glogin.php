<?php
/*
Create Google Sign-In auth url
*/

$gClient = new Google_Client();
$gClient->setClientId($cfg->get('gid'));
$gClient->setClientSecret($cfg->get('gsecret'));
$gClient->setRedirectUri($cfg->get('gcallback'));
$gClient->setScopes(array('email','profile'));

$gAuthUrl = $gClient->createAuthUrl();
?>


