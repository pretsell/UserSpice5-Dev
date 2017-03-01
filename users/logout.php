<?php

require_once 'init.php';

$prelogout = new StateResponse_PreLogout;
$prelogout->respond();
$user->logout();
$postlogout = new StateResponse_Logout;
$postlogout->respond();
