<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';

#Secures the page...required for page permission management
if (!securePage($_SERVER['PHP_SELF'])) { die(); }

if (Input::exists('get')) {
	$itemId=Input::get('id');

	$menu_title=Input::get('menu_title');
	/*
	*
	* DANGER - the menu_title is passed in and can be corrupted - great source for injection!!!
	*
	*/

	if (!empty($_GET['action'])) {
		$action=Input::get('action');

		if ($action=='newDropdown') {
			/*
			Inserts default "dropdown" entry
			*/
			$fields=array('menu_title'=>$menu_title,'parent'=>'-1','dropdown'=>'1','perm_level'=>'1','logged_in'=>'1','display_order'=>'99999','label'=>'New Dropdown','link'=>'','icon_class'=>'');
			$db->insert('menus',$fields);
		} elseif ($action=='newItem') {
			/*
			Inserts default "item" entry
			*/
			$fields=array('menu_title'=>$menu_title,'parent'=>'-1','dropdown'=>'0','perm_level'=>'1','logged_in'=>'1','display_order'=>'99999','label'=>'New Item','link'=>'','icon_class'=>'');
			$db->insert('menus',$fields);
		} elseif ($action=='delete') {
			$db->deleteById('menus',$itemId);
			Redirect::to('admin_menu.php?menu_title='.$item->menu_title);
		} else {
			Redirect::to('admin_menu.php?menu_title='.$item->menu_title);
		}
	}
	/*
	Query requested menu_title
	*/
	$menu_item_results = $db->query("SELECT * FROM menus WHERE menu_title=? ORDER BY display_order",[$menu_title]);
	$menu_items = $menu_item_results->results();
}

/*
Make indented tree
*/
$indentedMenuItems=prepareIndentedMenuTree($menu_item_results->results(true));
//dump($indentedMenuItems);
/*
$menu_items will contain array of associative arrays
*/
$menu_items_array=$indentedMenuItems;

/*
foreach below will loop through array and build array of objects from the associative arrays
*/
$menu_items=[];
foreach ($menu_items_array as $menu_item) {
	$menu_items[]=(object)$menu_item;
}

/*
Grab all records which are marked as dropdowns/parents
*/
$parent_results = $db->query("SELECT * FROM menus WHERE menu_title=? AND dropdown=1",[$menu_title]);
$parents = $parent_results->results();
$parentsSelect[-1]='No Parent';
foreach ($parents as $parent) {
	$parentsSelect[$parent->id]=$parent->label;
}

/*
Get groups and names
*/
$allGroups = fetchAllGroups();
$groupsSelect[0]='Unrestricted';
foreach ($allGroups as $group) {
	$groupsSelect[$group->id]=$group->name;
}

?>
<div class="row"> <!-- row for Users, Groups, Pages, Email settings panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=configGet('version')?></h1>
	<?php require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
</div> <!-- /.row -->

<div class="row">
	<div class="col-xs-12">
	<h2><?=$menu_title?> Menu</h2>
	<p class="text-center">
	<a href="admin_menu.php?menu_title=<?=$menu_title?>&action=newDropdown" class="btn btn-primary" role="button">New Dropdown</a>
	<a href="admin_menu.php?menu_title=<?=$menu_title?>&action=newItem" class="btn btn-primary" role="button">New Item</a>
	<a href="admin_menu.php?menu_title=<?=$menu_title?>&action=renumberOrder" class="btn btn-primary" role="button">Renumber Order</a>
	<a href="admin_menu.php?menu_title=<?=$menu_title?>" class="btn btn-primary" role="button">Refresh</a>
	</p>
	<div class="table-responsive">
		<table class="table table-bordered table-hover table-condensed">
			<thead><tr><th>ID</th><th>Label</th><th>Parent</th><th>Link</th><th>Dropdown</th><th>Authorized Groups</th><th>Logged In</th><th>Display Order</th><th>Icon Class</th><th>Action</th></tr></thead>
			<tbody>
			<?php
			$i=0;
			$itemCount=sizeof($menu_items);
			foreach ($menu_items as $item) {
			?>
				<tr>
				<td><?=$item->id?></td>

				<td><?=(($item->indent) ? '>>> ' : '').$item->label?></td>
				<td><?=$parentsSelect[$item->parent]?></td>
				<td><?=$item->link?></td>

				<td><?=($item->dropdown) ? 'Yes' : 'No';?></td>
				<td>
				<?php
				$sep = '';
				foreach (fetchGroupsByMenu($item->id) as $g) {
					#var_dump($g);
					echo $sep.$groupsSelect[$g->group_id];
					$sep = ",&nbsp;";
				}
				?>
			  </td>
				<td><?=($item->logged_in) ? 'Yes' : 'No';?></td>
				<td><?=$item->display_order?></td>


				<td><?=$item->icon_class?></td>
				<td>
					<a href="admin_menu_item.php?id=<?=$item->id?>&action=edit"><span class="fa fa-cog fa-lg"></span></a> /
					<a href="admin_menu.php?menu_title=<?=$menu_title?>&id=<?=$item->id?>&action=delete"><span class="fa fa-remove fa-lg"></span></a></td>
				</tr>
			<?php
			$i++;
			}
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
