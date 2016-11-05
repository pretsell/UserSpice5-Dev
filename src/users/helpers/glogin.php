<?php
/*
Create Google Sign-In auth url
*/

$gClient = new Google_Client();
$gClient->setClientId(configGet('gid'));
$gClient->setClientSecret(configGet('gsecret'));
$gClient->setRedirectUri(configGet('gcallback'));
$gClient->setScopes(array('email','profile'));

$gAuthUrl = $gClient->createAuthUrl();
?>


