<?php
/*
Create Facebook auth url
*/
$fb = new Facebook\Facebook([
  'app_id' => $site_settings->fbid,
  'app_secret' => $site_settings->fbsecret,
  'default_graph_version' => 'v2.2',
  ]);

$fbHelper = $fb->getRedirectLoginHelper();

//extra permissions are in the array() block at the end
$fbAuthUrl = $fbHelper->getLoginUrl($site_settings->fbcallback, array('email'));
?>