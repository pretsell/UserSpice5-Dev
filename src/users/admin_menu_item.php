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

if(Input::exists('post')){
	if(!Token::check(Input::get('csrf'))){
		$tokenError = lang('TOKEN');
die($tokenError);
	}
}

if (Input::exists('get')){
	$itemId=Input::get('id');
	if($itemId>=0){
		/*
		This is a valid ID so grab the record
		*/
		$item_results = $db->query("SELECT * FROM menus WHERE id=?",[$itemId]);
		$item = $item_results->first();
	}
}

if (Input::exists('get') && Input::exists('post')){
	$action=Input::get('action');
	if ($action=='edit'){
		/*
		Update the db with the new values
		*/

		$fields=array(
		'menu_title'=>$item->menu_title,
		'parent'=>Input::get('parent'),
		'dropdown'=>Input::get('dropdown'),
		'perm_level'=>Input::get('perm_level'),
		'logged_in'=>Input::get('logged_in'),
		'display_order'=>Input::get('display_order'),
		'label'=>Input::get('label'),
		'link'=>Input::get('link'),
		'icon_class'=>Input::get('icon_class')
		);
		$db->update('menus',$itemId,$fields);
		
		Redirect::to('admin_menu.php?menu_title='.$item->menu_title.'');
	}else{
		/*
		not a correct action so do nothing and send back to menu list
		*/
		Redirect::to('admin_menu.php?menu_title='.$item->menu_title.'');
	}
}

/*
Grab all records which are marked as dropdowns
*/
$dropdown_results = $db->query("SELECT * FROM menus WHERE menu_title=? AND dropdown=1",[$item->menu_title]);
$dropdowns = $dropdown_results->results();

/*
Get permission levels and names
*/
$permission_results = $db->query("SELECT * FROM permissions");
$permissions = $permission_results->results();

//dump($dropdowns);



?>
<div class="row"> <!-- row for Users, Permissions, Pages, Email settings panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=$site_settings->version?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
</div> <!-- /.row -->

<div class="row">
	<div class="col-xs-12">
	<h2>Menu Item</h2>

	<form name='edit_menu_item' action='admin_menu_item.php?id=<?=$itemId?>&action=edit' method='post'>

		<div class="form-group">
			<label>Parent</label>
			<select class="form-control" name="parent">
				<option value="-1" <?php if($item->parent == -1) echo 'selected="selected"'; ?> >No Parent</option>
				<?php
				foreach ($dropdowns as $dropdown){
				?>
					<option value="<?=$dropdown->id?>" <?php if($item->parent == $dropdown->id) echo 'selected="selected"'; ?> ><?=$dropdown->label?></option>
				<?php
				}
				?>
			</select>
		</div>
		
		<div class="form-group">
			<label>Dropdown</label>
			<select class="form-control" name="dropdown">
				<option value="1" <?php if($item->dropdown == 1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if($item->dropdown == 0) echo 'selected="selected"'; ?> >No</option>
			</select>			
		</div>

		<div class="form-group">
			<label>Perm Level</label>
			<select class="form-control" name="perm_level">
				<option value="0" <?php if($item->perm_level == 0) echo 'selected="selected"'; ?> >No Perms Required</option>
				<?php
				foreach ($permissions as $permission){
				?>
					<option value="<?=$permission->id?>" <?php if($item->perm_level == $permission->id) echo 'selected="selected"'; ?> ><?=$permission->name?></option>
				<?php
				}
				?>
			</select>
		</div>
		
		<div class="form-group">
			<label>User must be logged in</label>
			<select class="form-control" name="logged_in">
				<option value="1" <?php if($item->logged_in == 1) echo 'selected="selected"'; ?> >Yes</option>
				<option value="0" <?php if($item->logged_in == 0) echo 'selected="selected"'; ?> >No</option>
			</select>
		</div>

		<div class="form-group">
			<label>Display Order</label>
			<input  class='form-control' type='text' name='display_order' value='<?=$item->display_order?>' />
		</div>

		<div class="form-group">
			<label>Label</label>
			<input  class='form-control' type='text' name='label' value='<?=$item->label?>' />
		</div>		

		<div class="form-group">
			<label>Link</label>
			<input  class='form-control' type='text' name='link' value='<?=$item->link?>' />
		</div>	
		
		<div class="form-group">
			<label>Icon Class</label>
			<input  class='form-control' type='text' name='icon_class' value='<?=$item->icon_class?>' />
		</div>	
		
		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<p class="text-center"><input class='btn btn-primary' name='update' type='submit' value='Update' class='submit' />
		<a class="btn btn-info" href="admin_menu.php?menu_title=<?=$item->menu_title?>">Cancel</a></p>

	</form>	




</div>
</div>

<!-- footers -->
<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
