<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';

clearstatcache();
/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}
checkToken();

?>
<div class="row "> <!-- rows for Info Panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
	<div class="col-xs-12">
	<h2>Server Status</h2>
	</div>
	<div class="col-xs-12 col-md-6">
	<div class="panel panel-default">
	<div class="panel-heading"><strong>Versions</strong></div>
	<div class="panel-body">
	PHP Version: <?=phpversion()?><br/>
	MYSQL Version: <?=$db->getAttribute("PDO::ATTR_SERVER_VERSION");?><br/>
	Apache Version: Cannot Read<br/>
	</div>
	</div><!--/panel-->
	</div> <!-- /col -->

	<div class="col-xs-12 col-md-6">
	<div class="panel panel-default">
	<div class="panel-heading"><strong>General</strong></div>
	<div class="panel-body">
	UserSpice ABS_US_ROOT: <?=ABS_US_ROOT?><br/>
	UserSpice US_URL_ROOT: <?=US_URL_ROOT?><br/>
	Config Writable: <?=((@file_exists(ABS_US_ROOT.US_URL_ROOT.'users/init.php') &&  @is_writable( ABS_US_ROOT.US_URL_ROOT.'users/init.php')) ? "Writeable" : "NOT Writable")?>
	</div>
	</div><!--/panel-->
	</div> <!-- /col -->

	<div class="col-xs-12 col-md-6">
	<div class="panel panel-default">
	<div class="panel-heading"><strong>PHP ini Important Parameters</strong></div>
	<div class="panel-body">
	Short Open Tag: <?=ini_get('short_open_tag')?><br/>
	</div>
	</div><!--/panel-->
	</div> <!-- /col -->

	<div class="col-xs-12 col-md-6">
	<div class="panel panel-default">
	<div class="panel-heading"><strong>PHP Extensions Loaded</strong></div>
	<div class="panel-body">
	<?php
	$extensions=get_loaded_extensions();
	foreach($extensions as $extension){
	?>
	<?=$extension?> Available<br/>
	<?php
	}


	?>
	</div>
	</div><!--/panel-->


	</div> <!-- /col -->
</div> <!-- /row -->

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
