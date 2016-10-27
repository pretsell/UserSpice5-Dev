<?php
/*
Create Facebook auth url
*/
$fb = new Facebook\Facebook([
  'app_id' => $$cfg->get('fbid'),
  'app_secret' => $$cfg->get('fbsecret'),
  'default_graph_version' => 'v2.2',
  ]);

$fbHelper = $fb->getRedirectLoginHelper();

//extra permissions are in the array() block at the end
$fbAuthUrl = $fbHelper->getLoginUrl($$cfg->get('fbcallback'), array('email'));
?>