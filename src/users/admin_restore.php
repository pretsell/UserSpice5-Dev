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

$errors = $successes = [];

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}
checkToken();

if(!empty($_POST['restore'])){
	if(Input::get('restore_type') == 'us_files'){
		/*
		1. Verify the file exists
		2. Verify the hash matches (can be added later)
		2b. Create temp directory same as filename but without .zip
		3. Extract file to temp directory
		4. Rename users/ folder to users.old/
		5. rename backup folder to correct path for users/
		*/
		$restoreFile=ABS_US_ROOT.US_URL_ROOT.$$cfg->get('backup_dest').Input::get('restore_file');
		$fileObjects=explode('/',$restoreFile);
		$restoreFilename=end($fileObjects);
		$restoreDest=ABS_US_ROOT.US_URL_ROOT.$$cfg->get('backup_dest').substr($restoreFilename,0,strlen($restoreFilename)-4).'/';

		/*
		Extract Zip File
		*/
		if(extractZip($restoreFile,$restoreDest)){
			$successes[]='Backup extraction successful';

			/*
			Rename original users/ to users.old/
			*/
			if(rename(ABS_US_ROOT.US_URL_ROOT.'users/',ABS_US_ROOT.US_URL_ROOT.'users.old/')){
				$successes[]='Successfully renamed existing "users/" folder to "users.old/".';

				/*
				Move the restore data to where it needs to be at users/
				*/
				if(rename($restoreDest.'files/users/',ABS_US_ROOT.US_URL_ROOT.'users/')){
					$successes[]='Successfully copied the restore files.';
				}else{
					$errors[]='Could not move restore data.';
				}
			}else{
				$errors[]='Could not rename existing "users/" folder.';
			}
		}else{
			$errors[]='Count not extract the files';
		}
	}elseif(Input::get('restore_type') == 'db_us_files'){
		/*
		1. Verify the file exists
		2. Verify the hash matches (can be added later)
		2b. Create temp directory same as filename but without .zip
		3. Extract file to temp directory
		4. Rename users/ folder to users.old/
		5. rename backup folder to correct path for users/
		*/
		$restoreFile=ABS_US_ROOT.US_URL_ROOT.$$cfg->get('backup_dest').Input::get('restore_file');
		$fileObjects=explode('/',$restoreFile);
		$restoreFilename=end($fileObjects);
		$restoreDest=ABS_US_ROOT.US_URL_ROOT.$$cfg->get('backup_dest').substr($restoreFilename,0,strlen($restoreFilename)-4).'/';

		/*
		Extract Zip File
		*/
		if(extractZip($restoreFile,$restoreDest)){
			$successes[]='Backup extraction successful';

			/*
			Rename original users/ to users.old/
			*/
			if(rename(ABS_US_ROOT.US_URL_ROOT.'users/',ABS_US_ROOT.US_URL_ROOT.'users.old/')){
				$successes[]='Successfully renamed existing "users/" folder to "users.old/".';
				/*
				Move the restore data to where it needs to be at users/
				*/
				if(rename($restoreDest.'files/users/',ABS_US_ROOT.US_URL_ROOT.'users/')){
					$successes[]='Successfully copied the restore files.';
				}else{
					$errors[]='Could not move restore data.';
				}
			}else{
				$errors[]='Could not rename existing "users/" folder.';
			}
			/*
			Restore all the DB files, one at a time
			*/
			foreach ($us_tables as $table){
				$sqlFile=$restoreDest.'sql/'.$table.'.sql';
				if(importSqlFile($sqlFile)){
					$successes[]='Successfully executed: '.$sqlFile;
				}else{
					$errors[]='Did NOT execute correctly: '.$sqlFile;
				}
			}
		}else{
			$errors[]='Count not extract the files';
		}

	}elseif(Input::get('restore_type') == 'db_only'){
		/*
		1. Verify the file exists
		2. Verify the hash matches (can be added later)
		2b. Create temp directory same as filename but without .zip
		3. Extract file to temp directory
		4. Rename users/ folder to users.old/
		5. rename backup folder to correct path for users/
		*/
		$restoreFile=ABS_US_ROOT.US_URL_ROOT.$$cfg->get('backup_dest').Input::get('restore_file');
		$fileObjects=explode('/',$restoreFile);
		$restoreFilename=end($fileObjects);
		$restoreDest=ABS_US_ROOT.US_URL_ROOT.$$cfg->get('backup_dest').substr($restoreFilename,0,strlen($restoreFilename)-4).'/';

		/*
		Extract Zip File
		*/
		if(extractZip($restoreFile,$restoreDest)){
			$successes[]='Backup extraction successful';
			/*
			Restore all the DB files, one at a time
			*/
			foreach ($us_tables as $table){
				$sqlFile=$restoreDest.'sql/'.$table.'.sql';
				if(importSqlFile($sqlFile)){
					$successes[]='Successfully executed: '.$sqlFile;
				}else{
					$errors[]='Did NOT execute correctly: '.$sqlFile;
				}
			}
		}else{
			$errors[]='Count not extract the files';
		}
	}else{
		/*
		Unknown state? Do nothing.
		*/
	}
}elseif(!empty($_POST['save'])){

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
$allBackupFiles=glob(ABS_US_ROOT.US_URL_ROOT.$$cfg->get('backup_dest').'backup*.zip');
$allBackupFilesSize=[];
foreach($allBackupFiles as $backupFile){
	$allBackupFilesSize[]=filesize($backupFile);
}

?>
<div class="row"> <!-- row for Users, Permissions, Pages, Email settings panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$$cfg->get('version')?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
</div> <!-- /.row -->

<div class="row">
	<div class="col-xs-12">
	<h2>Restore</h2>
	<?=display_successes($successes);?>
	<?=display_errors($errors);?>
	<form class="" action="admin_restore.php" name="backup" method="post">

		<!-- restore_type Option -->
		<div class="form-group">
			<label for="restore_type">Restore Type</label>
			<select id="restore_type" class="form-control" name="restore_type">
				<option value="us_files">UserSpice Files Only</option>
				<option value="db_us_files">Database and UserSpice Files</option>
				<option value="db_only">Database Only</option>
			</select>
		</div>
		<!-- restore_file Option -->
		<div class="form-group">
			<label for="restore_file">Restore File</label>
			<select id="restore_file" class="form-control" name="restore_file">
			<?php
			$i=0;
			foreach ($allBackupFiles as $backupFile){
				$filename=end(explode('/',$backupFile));
			?>
				<option value="<?=$filename?>"><?=$filename.' ('.$allBackupFilesSize[$i].' bytes)'?></option>
			<?php
			$i++;}
			?>
			</select>
		</div>

		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />
		<p>
		<input class='btn btn-primary' type='submit' name="restore" value='Restore' />
		</p>

	</form>


	</div>
</div>

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
