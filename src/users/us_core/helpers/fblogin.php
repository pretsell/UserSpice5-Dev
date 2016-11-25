<?php
/*
Create Facebook auth url
*/
$fb = new Facebook\Facebook([
  'app_id' => configGet('fbid'),
  'app_secret' => configGet('fbsecret'),
  'default_graph_version' => 'v2.2',
  ]);

$fbHelper = $fb->getRedirectLoginHelper();

//extra permissions are in the array() block at the end
$fbAuthUrl = $fbHelper->getLoginUrl(configGet('fbcallback'), array('email'));
?>