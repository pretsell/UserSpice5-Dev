<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/
/*
Set longer execution time and larger memory limit to deal with large backup sets
*/
ini_set('max_execution_time', 600);
ini_set('memory_limit','1024M');

require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';

$errors=[];
$successes=[];

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}

if(Input::exists()){
	if(!Token::check(Input::get('csrf'))){
		die('Token doesn\'t match!');
	}
}
if(!empty($_POST['backup'])){
	/*
	Create backup destination folder: $site_settings->backup_dest
	*/
	$destPath=ABS_US_ROOT.US_URL_ROOT.$site_settings->backup_dest;
	if(!file_exists($destPath)){
		if (mkdir($destPath)){
			$destPathSuccess=true;
			$successes[]='Destination path created.';
		}else{
			$destPathSuccess=false;
			$errors[]='Destination path could not be created due to unknown error.';
		}
	}else{
		$successes[]='Destination path already existed. Using the existing folder.';
	}

	/*
	Generate backup path
	*/
	$backupDateTimeString=date("Y-m-d\TH-i-s");
	$backupPath=ABS_US_ROOT.US_URL_ROOT.$site_settings->backup_dest.'backup_'.$backupDateTimeString.'/';

	if(!file_exists($backupPath)){
		if (mkdir($backupPath)){
			$backupPathSuccess=true;
		}else{
			$backupPathSuccess=false;
		}
	}

	if($backupPathSuccess){
		/*
		Since the backup path is just created with a timestamp, no need to check if these subfolders exist or if they are writable
		*/
		mkdir($backupPath.'files');
		mkdir($backupPath.'sql');
	}

	if($backupPathSuccess && Input::get('backup_source') == 'db_all_files'){
		/*
		Generate list of files in ABS_US_ROOT.US_URL_ROOT including files starting with .
		*/
		$allFilesFolders=glob(ABS_US_ROOT.US_URL_ROOT.'{,.}*', GLOB_BRACE);
		$backupItems=[];
		/*
		Cycle through each item and check to see if it should be excluded (starting with backup, or is /. or /..)
		*/
		foreach ($allFilesFolders as $fileFolder) {
			if((strpos($fileFolder, ABS_US_ROOT.US_URL_ROOT.'backup_') !== 0) && ($fileFolder != ABS_US_ROOT.US_URL_ROOT.'.') && ($fileFolder != ABS_US_ROOT.US_URL_ROOT.'..')){
				$backupItems[]=$fileFolder;
			}
		}
		if(backupObjects($backupItems,$backupPath.'files/')){
			$successes[]='Backup was successful.';
		}else{
			$errors[]='Backup failed.';
		}

		if(backupUsTables($us_tables,$backupPath.'sql/')){
			$successes[]='SQL dumps were successful.';
		}else{
			$errors[]='SQL dumps failed.';
		}
		$targetZipFile=backupZip($backupPath,true);
		if($targetZipFile){
			$successes[]='DB and Files Zipped';
			$backupZipHash=hash_file('sha1', $targetZipFile);
			$backupZipHashFilename=substr($targetZipFile,0,strlen($targetZipFile)-4).'_SHA1_'.$backupZipHash.'.zip';
			if(rename($targetZipFile,$backupZipHashFilename)){
				$successes[]='File SHA1 hashed and renamed to: '.$backupZipHashFilename;
			}else{
				$errors[]='Could not rename backup zip file to contain hash value.';
			}
		}else{
			$errors[]='Error creating zip file';
		}

	}elseif($backupPathSuccess && Input::get('backup_source') == 'db_us_files'){
		/*
		Generate list of files in ABS_US_ROOT.US_URL_ROOT including files starting with .
		*/
		$backupItems=[];
		$backupItems[]=ABS_US_ROOT.US_URL_ROOT.'users';
		$backupItems[]=ABS_US_ROOT.US_URL_ROOT.'usersc';

		if(backupObjects($backupItems,$backupPath.'files/')){
			$successes[]='Backup was successful.';
		}else{
			$errors[]='Backup failed.';
		}
		if(backupUsTables($us_tables,$backupPath.'sql/')){
			$successes[]='SQL dumps were successful.';
		}else{
			$errors[]='SQL dumps failed.';
		}
		$targetZipFile=backupZip($backupPath,true);
		if($targetZipFile){
			$successes[]='DB and US Files Zipped';
			$backupZipHash=hash_file('sha1', $targetZipFile);
			$backupZipHashFilename=substr($targetZipFile,0,strlen($targetZipFile)-4).'_SHA1_'.$backupZipHash.'.zip';
			if(rename($targetZipFile,$backupZipHashFilename)){
				$successes[]='File SHA1 hashed and renamed to: '.$backupZipHashFilename;
			}else{
				$errors[]='Could not rename backup zip file to contain hash value.';
			}
		}else{
			$errors[]='Error creating zip file';
		}
	}elseif($backupPathSuccess && Input::get('backup_source') == 'db_only'){
		if(backupUsTables($us_tables,$backupPath.'sql/')){
			$successes[]='SQL dumps were successful.';
		}else{
			$errors[]='SQL dumps failed.';
		}
		$targetZipFile=backupZip($backupPath,true);
		if($targetZipFile){
			$successes[]='DB and US Files Zipped';
			$backupZipHash=hash_file('sha1', $targetZipFile);
			$backupZipHashFilename=substr($targetZipFile,0,strlen($targetZipFile)-4).'_SHA1_'.$backupZipHash.'.zip';
			if(rename($targetZipFile,$backupZipHashFilename)){
				$successes[]='File SHA1 hashed and renamed to: '.$backupZipHashFilename;
			}else{
				$errors[]='Could not rename backup zip file to contain hash value.';
			}
		}else{
			$errors[]='Error creating zip file';
		}
	}elseif(!backupPathSuccess){
		$errors[]='Backup path already exists or could not be created.';
	}else{
		/*
		Unknown state? Do nothing.
		*/
	}
}elseif(!empty($_POST['save'])){
	if($site_settings->backup_dest != $_POST['backup_dest']) {
		$backup_dest = Input::get('backup_dest');
		$fields=array('backup_dest'=>$backup_dest);
		$db->update('settings',1,$fields);
	}

	Redirect::to('admin_backup.php');
}else{
	/*
	other form?
	*/
}

if(Input::exists('get')){

}

/*
Get array of existing backup zip files
*/
$allBackupFiles=glob(ABS_US_ROOT.US_URL_ROOT.$site_settings->backup_dest.'backup*.zip');
$allBackupFilesSize=[];
foreach($allBackupFiles as $backupFile){
	$allBackupFilesSize[]=filesize($backupFile);
}

?>
<div class="row"> <!-- row for Users, Groups, Pages, Email settings panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
</div> <!-- /.row -->

<div class="row">
	<div class="col-xs-12">
	<h2>Backup</h2>
	<?=display_successes($successes);?>
	<?=display_errors($errors);?>
	<form class="" action="admin_backup.php" name="backup" method="post">

		<!-- backup_dest Option -->
		<div class="form-group">
			<label class="control-label" for="backup_dest">Backup Destination (relative to the z_us_root.php file)</label>
			<input  class="form-control" type="text" name="backup_dest" id="backup_dest" placeholder="Backup Destination" value="<?=$site_settings->backup_dest?>">
		</div>
		<p><input class='btn btn-primary' type='submit' name="save" value='Save Settings' /></p>

		<!-- backup_source Option -->
		<div class="form-group">
			<label for="backup_source">Backup Source</label>
			<select id="backup_source" class="form-control" name="backup_source">
				<option value="db_us_files">Database and UserSpice Files</option>
				<option value="db_all_files">Database and All Files</option>
				<option value="db_only">Database Only</option>
			</select>
		</div>
		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />
		<p>
		<input class='btn btn-primary' type='submit' name="backup" value='Backup' />

		</p>
	</form>

	<h2>Existing Backups (<?=sizeof($allBackupFiles)?>)</h2>
	<div class="table-responsive">
		<table class="table table-bordered table-hover">
			<thead><tr><th>Backup File</th><th>Size (bytes)</th></tr></thead>
			<tbody>
			<?php
			$i=0;
			foreach ($allBackupFiles as $backupFile){
				$objectName=explode('/',$backupFile);
				$filename=end($objectName);
			?>
				<tr><td><a href="<?=US_URL_ROOT.$site_settings->backup_dest.$filename?>"><?=$filename?></a></td><td><?=$allBackupFilesSize[$i]?></td></tr>
			<?php
			$i++;}
			?>
			</tbody>
		</table>
	</div>

	</div>
</div>

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
