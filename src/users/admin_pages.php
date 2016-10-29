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

$errors = [];
$successes = [];

/*
Get line from z_us_root.php that starts with $path
*/
$file = fopen(ABS_US_ROOT.US_URL_ROOT."z_us_root.php","r");
while(!feof($file)){
	$currentLine=fgets($file);
	if (substr($currentLine,0,5)=='$path'){
		//echo $currentLine;
		//if here, then it found the line starting with $path so break to preserve $currentLine value
		break;
	}
}
fclose($file);

//sample text: $path=('/','/users/','/usersc/');
//Get array of paths, with quotes removed
$lineLength=strlen($currentLine);
$pathString=str_replace("'","",substr($currentLine,7,$lineLength-11));
$paths=explode(',',$pathString);

$pages=[];

//Get list of php files for each $path
foreach ($paths as $path){
	$rows=getPathPhpFiles(ABS_US_ROOT,US_URL_ROOT,$path);
 	foreach ($rows as $row){
		$pages[]=$row;
	} 
}

$dbpages = fetchAllPages(); //Retrieve list of pages in pages table

$count = 0;
$dbcount = count($dbpages);
$creations = array();
$deletions = array();

foreach ($pages as $page) {
    $page_exists = false;
    foreach ($dbpages as $k => $dbpage) {
        if ($dbpage->page === $page) {
            unset($dbpages[$k]);
            $page_exists = true;
            break;
        }
    }
    if (!$page_exists) {
        $creations[] = $page;
    }
}

// /*
//  * Remaining DB pages (not found) are to be deleted.
//  * This function turns the remaining objects in the $dbpages
//  * array into the $deletions array using the 'id' key.
//  */
$deletions = array_column(array_map(function ($o) {return (array)$o;}, $dbpages), 'id');

$deletes = '';
for($i = 0; $i < count($deletions);$i++) {
	$deletes .= $deletions[$i] . ',';
}
$deletes = rtrim($deletes,',');
//Enter new pages in DB if found
if (count($creations) > 0) {
    createPages($creations);
}
// //Delete pages from DB if not found
if (count($deletions) > 0) {
    deletePages($deletes);
}

//Update $dbpages
$dbpages = fetchAllPages();

?>

<!-- Page Heading -->
<div class="row">
<div class="col-xs-12">
<h1 class="text-center">UserSpice Dashboard <?=$cfg->get('version')?></h1>
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
</div>

<div class="col-xs-12">

<h2>Manage Page Access</h2>
<div class="input-group col-xs-12">
<!-- USE TWITTER TYPEAHEAD JSON WITH API TO SEARCH -->
<input class="form-control" id="system-search" name="q" placeholder="Search Pages..." required>
<span class="input-group-btn">
  <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
</span>
</div>
<br>
<table class='table table-hover table-list-search'>
    <th>Id</th><th>Page</th><th>Access</th>

    <?php
    //Display list of pages
	$count=0;
    foreach ($dbpages as $page){
		?>
		<tr><td><?=$dbpages[$count]->id?></td>
		<td><a href ='admin_page.php?id=<?=$dbpages[$count]->id?>'><?=$dbpages[$count]->page?></a></td>
		<td>
		<?php
		//Show public/private setting of page
		if($dbpages[$count]->private == 0){
			echo "Public";
		}else {
			echo "Private";
		}
		?>
		</td></tr>
		<?php
		$count++;
    }?>
</table>
</div>
<!-- /.row -->
</div>


<!-- Content Ends Here -->
<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script src="js/search.js" charset="utf-8"></script>

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
