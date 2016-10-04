<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?=US_URL_ROOT?>">UserSpice Demo</a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li><a href="<?=US_URL_ROOT?>"><i class="fa fa-lg fa-home" aria-hidden="true"></i> Home</a></li>
				<li><a href="gallery.php"><i class="fa fa-lg fa-picture-o" aria-hidden="true"></i> Gallery</a></li>
				<li><a href="contact.php"><i class="fa fa-lg fa-envelope-o" aria-hidden="true"></i> Contact</a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
			<?php
			if ($user->isLoggedIn()){
			?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="User Actions"><i class="fa fa-lg fa-user" aria-hidden="true"></i> <span id='loginstatus'><?php echo $user->data()->username;?></span><span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li class="dropdown-header">User Actions</li>
						<li><a href="profile.php"><i class="fa fa-lg fa-cog" aria-hidden="true"></i> Profile</a></li>
						<?php
						if (checkMenu(2,$user->data()->id)){
						?>
						<li role="separator" class="divider"></li>
						<li><a href="users/admin.php"><i class="fa fa-lg fa-cogs" aria-hidden="true"></i> Admin</a></li>
						<?php
						}
						?>
						<li role="separator" class="divider"></li>
						<li><a href="users/logout.php"><i class="fa fa-lg fa-sign-out" aria-hidden="true"></i> Sign Out</a></li>
					</ul>
				</li>
			<?php
			}else{
			?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="User Actions"><i class="fa fa-lg fa-user" aria-hidden="true"></i> <span id='loginstatus'>Not Logged In</span><span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li class="dropdown-header">User Actions</li>
						<li><a href="login.php"><i class="fa fa-lg fa-sign-in" aria-hidden="true"></i> Sign In</a></li>
						<li><a href="join.php"><i class="fa fa-lg fa-user-plus" aria-hidden="true"></i> Register</a></li>
						<li role="separator" class="divider"></li>
						<li><a href="forgot_password.php"><i class="fa fa-lg fa-user-plus" aria-hidden="true"></i> Forgot Password</a></li>
						<li><a href="verify_resend.php"><i class="fa fa-lg fa-user-plus" aria-hidden="true"></i> Resend Email Verification</a></li>
					</ul>
				</li>				
			<?php	
			}
			?>
			</ul>
		</div><!--/.nav-collapse -->
	</div>
</nav>