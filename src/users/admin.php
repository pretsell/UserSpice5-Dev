<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}
checkToken();

//PHP Goes Here!
delete_user_online(); //Deletes sessions older than 24 hours

//Find users who have logged in in X amount of time.
$date = date("Y-m-d H:i:s");

$hour = date("Y-m-d H:i:s", strtotime("-1 hour", strtotime($date)));
$today = date("Y-m-d H:i:s", strtotime("-1 day", strtotime($date)));
$week = date("Y-m-d H:i:s", strtotime("-1 week", strtotime($date)));
$month = date("Y-m-d H:i:s", strtotime("-1 month", strtotime($date)));

$last24=time()-86400;

$recentUsersQ = $db->query("SELECT * FROM users_online WHERE timestamp > ? ORDER BY timestamp DESC",array($last24));
$recentUsersCount = $recentUsersQ->count();
$recentUsers = $recentUsersQ->results();

$usersHourQ = $db->query("SELECT * FROM users WHERE last_login > ?",array($hour));
$usersHour = $usersHourQ->results();
$hourCount = $usersHourQ->count();

$usersTodayQ = $db->query("SELECT * FROM users WHERE last_login > ?",array($today));
$dayCount = $usersTodayQ->count();
$usersDay = $usersTodayQ->results();

$usersWeekQ = $db->query("SELECT username FROM users WHERE last_login > ?",array($week));
$weekCount = $usersWeekQ->count();

$usersMonthQ = $db->query("SELECT username FROM users WHERE last_login > ?",array($month));
$monthCount = $usersMonthQ->count();

?>
<div class="row "> <!-- rows for Info Panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=configGet('version')?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
	<div class="col-xs-12">
	<h2>Information</h2>
	</div>
	<div class="col-xs-12 col-md-6">
	<div class="panel panel-default">
	<div class="panel-heading"><strong>All Users</strong> <span class="small">(Who have logged in)</span></div>
	<div class="panel-body text-center">
	<div class="row">
		<div class="col-xs-3 "><h3><?=$hourCount?></h3><p>per hour</p></div>
		<div class="col-xs-3"><h3><?=$dayCount?></h3><p>per day</p></div>
		<div class="col-xs-3 "><h3><?=$weekCount?></h3><p>per week</p></div>
		<div class="col-xs-3 "><h3><?=$monthCount?></h3><p>per month</p></div>
	</div>
	</div>
	</div><!--/panel-->

	<div class="panel panel-default">
	<div class="panel-heading"><strong>All Visitors</strong> <span class="small">(Whether logged in or not)</span></div>
	<div class="panel-body">
	<?php  if(configGet('track_guest') == 1){ ?>
	<?="In the last 30 minutes, the unique visitor count was ".count_users()."<br>";?>
	<?php }else{ ?>
	Guest tracking off. Turn "Track Guests" on below for advanced tracking statistics.
	<?php } ?>
	</div>
	</div><!--/panel-->

	</div> <!-- /col -->

	<div class="col-xs-12 col-md-6">
	<div class="panel panel-default">
	<div class="panel-heading"><strong>Logged In Users</strong> <span class="small">(past 24 hours)</span></div>
	<div class="panel-body">
	<div class="uvistable table-responsive">
	<table class="table">
	<?php if(configGet('track_guest') == 1){ ?>
	<thead><tr><th>Username</th><th>IP</th><th>Last Activity</th></tr></thead>
	<tbody>

	<?php foreach($recentUsers as $v1){
		$user_id=$v1->user_id;

		if($user_id!=null){
			$username=name_from_id($user_id);
		}else{
			$username='guest';
		}
		$timestamp=date("Y-m-d H:i:s",$v1->timestamp);
		$ip=$v1->ip;

		if ($user_id==0){
			$username="guest";
		}

		if ($user_id==0){?>
			<tr><td><?=$username?></td><td><?=$ip?></td><td><?=$timestamp?></td></tr>
		<?php }else{ ?>
			<tr><td><a href="admin_user.php?id=<?=$user_id?>"><?=$username?></a></td><td><?=$ip?></td><td><?=$timestamp?></td></tr>
		<?php } ?>

	<?php } ?>

	</tbody>
	<?php } else { echo 'Guest tracking off. Turn "Track Guests" on below for advanced tracking statistics.'; } ?>
	</table>
	</div>
	</div>
	</div><!--/panel-->
	</div> <!-- /col2/2 -->
</div> <!-- /row -->

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
