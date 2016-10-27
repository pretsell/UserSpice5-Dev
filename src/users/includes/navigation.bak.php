<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/
?>

<nav class="navbar navbar-default">
<div class="container-fluid">
  <div class="navbar-header">
	<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	</button>
	<a href="<?=US_URL_ROOT?>"><img src="<?=US_URL_ROOT?>users/images/logo.png"></img></a>
  </div>
  <div id="navbar" class="navbar-collapse collapse">
	<ul class="nav navbar-nav navbar-right">
	  <li><a href="<?=US_URL_ROOT?>"><span class="fa fa-fw fa-home"></span> Home</a></li>

	  <?php 
	  if ($user->isLoggedIn()){
	  ?>
	  <li class="dropdown">
		<a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="fa fa-fw fa-user"></span> <?=$user->data()->username ?> <span class="caret"></span></a>
		<ul class="dropdown-menu">
			<li><a href="<?=US_URL_ROOT?>users/profile.php">Profile</a></li>
			<li><a href="<?=US_URL_ROOT?>users/logout.php">Logout</a></li>
		</ul>
	  </li>
	  <?php }else{ ?>
		<li><a href="<?=US_URL_ROOT?>users/join.php">Register</a></li>
		<li><a href="<?=US_URL_ROOT?>users/login.php">Log In</a></li>
	  <?php } ?>
	  <?php
	  if (checkMenu(2,$user->data()->id)){
	  ?>
		<li><a href="<?=US_URL_ROOT?>users/admin.php"><span class="fa fa-fw fa-cogs"></span> Dashboard</a></li>
	  <?php
	  }
	  ?>
	  <li class="dropdown">
		<a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="fa fa-life-ring"></span> Help <span class="caret"></span></a>
		<ul class="dropdown-menu">
		  <li><a href="<?=US_URL_ROOT?>users/forgot_password.php">Forgot Password</a></li>
		  <?php
		  if ($$cfg->get('email_act')==1){
		  ?>
			<li><a href="<?=US_URL_ROOT?>users/verify_resend.php">Resend Email Verification</a></li>
		  <?php
		  }
		  ?>
		</ul>
	  </li>
	</ul>
  </div><!--/.nav-collapse -->
</div><!--/.container-fluid -->
</nav>
