<?php
checkToken();

$parentPage = 'admin_pages.php';
$pageId = Input::get('id');

//Check if selected pages exist
if(!pageIdExists($pageId)) {
  Redirect::to("admin_pages.php"); die();
}

$pageGroups = fetchGroupsByPage($pageId); // groups with auth to access page
$myForm = new Form ([
    'general' => new Form_Panel([
        'page' => new FormField_Text([
            'display' => lang('PAGE_PATH'),
            'disabled' => true,
            'hint_text' => lang('READ_ONLY'),
            'field' => 'page',
        ]),
        'private' => new FormField_Select([
            'dbfield' => 'private',
            #'isdbfield' => true,
            #'new_valid' => [],
            'display' => lang('PUBLIC_OR_PRIVATE'),
            'hint_text' => lang('CHOOSE_FROM_LIST_BELOW'),
            'repeat' => [
                ['id'=>1, 'name'=>lang('PRIVATE')],
                ['id'=>0, 'name'=>lang('PUBLIC')],
            ],
        ])
    ], [
        'head' => '<h4>'.lang('PAGE_INFO_TITLE').'</h4>',
        'Panel_Class' => 'panel-default col-xs-12',
    ]),
    'accessRow' => new Form_Row([
        'remove' => new Form_Panel([
            'removeGroup' => new FormField_Table([
                'field' => 'removeGroup',
                'table_head_cells' => '<th>'.lang('MARK_TO_DELETE').'</th>'.
                    '<th>'.lang('GROUP').'</th>',
                'table_data_cells' => '<td>{CHECKBOX_ID}</td>'.
                    '<td><a href="admin_group.php?id={GROUP_ID}">{NAME}</a></td>',
                #'checkbox_label' => lang('MARK_TO_DELETE'),
                'nodata' => '<p>'.lang('NO_GROUP_ACCESS').'</p>',
                'table_class' => 'table-condensed table-hover',
                'isdbfield' => false,
                # repeating data will be loaded below
            ])
        ], [
            'head' => '<h4>'.lang('PAGE_DEL_GROUP_ACCESS').'</h4>',
            'Panel_Class' => 'panel-default col-xs-12 col-sm-6',
        ]),
        'add' => new Form_Panel([
            'addGroup' => new FormField_Table([
                'field' => 'addGroup',
                'table_head_cells' => '<th>'.lang('MARK_TO_ADD').'</th>'.
                    '<th>'.lang('GROUP').'</th>',
                'table_data_cells' => '<td>{CHECKBOX_ID}</td>'.
                    '<td><a href="admin_group.php?id={ID}">{NAME}</a></td>',
                #'checkbox_label' => lang('MARK_TO_ADD'),
                'nodata' => '<p>'.lang('NO_GROUP_WITHOUT_ACCESS').'</p>',
                'table_class' => 'table-condensed table-hover',
                'isdbfield' => false,
                # repeating data will be loaded below
            ])
        ], [
            'head' => '<h4>'.lang('PAGE_ADD_GROUP_ACCESS').'</h4>',
            'Panel_Class' => 'panel-default col-xs-12 col-sm-6',
        ]),
    ]),
    'btnRow' => new Form_Row([
        'save' => new FormField_ButtonSubmit([
            'field' => 'save',
            'display' => lang('PAGE_SAVE'),
        ]),
        'save_and_return' => new FormField_ButtonSubmit([
            'field' => 'save_and_return',
            'display' => lang('PAGE_SAVE_AND_RETURN'),
        ]),
    ])
], [
    'title' => lang('ADMIN_PAGE_TITLE'),
    'dbtable' => 'pages',
    'Keep_AdminDashBoard' => true,
]);

$pageDetails = fetchPageDetails($pageId); //Fetch information specific to page
$myForm->setFieldValues($pageDetails);
// Update the database from $_POST
if(Input::exists()) {

    // update `private` column
    $myForm->setNewValues($_POST);
    if ($myForm->updateIfValid($pageId, $errors) == Form::UPDATE_SUCCESS) {
        $successes[] = lang('PAGE_UPDATE_SUCCESSFUL');
    }

	//Remove group's access to page
	if ($remove = Input::get('removeGroup')) {
		if ($delCount = deleteGroupsPages($pageId, $remove)) {
			$successes[] = lang('PAGE_ACCESS_REMOVED', $delCount);
		} else {
			$errors[] = lang('SQL_ERROR');
		}
	}

	//give group access to page
	if ($add = Input::get('addGroup')) {
		if ($addCount = addGroupsPages($pageId, $add) > 0) {
			$successes[] = lang('PAGE_ACCESS_ADDED', $addCount);
		}
	}
	$pageDetails = fetchPageDetails($pageId);

    if (Input::get('save_and_return')) {
        Redirect::to($parentPage);
    }
}
$pageGroups = fetchGroupsByPage($pageId); // groups with auth to access page
$notPageGroups = fetchGroupsByNotPage($pageId); // groups with NO auth to access page
$groupData = fetchAllGroups();
$myForm->getField('removeGroup')->setRepData($pageGroups);
$myForm->getField('addGroup')->setRepData($notPageGroups);
echo $myForm->getHTML(['errors'=>$errors, 'successes'=>$successes]);

?>
					<div class="panel-heading"><strong>Information</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<label>ID:</label>
						<?= $pageDetails->id; ?>
						</div>
						<div class="form-group">
						<label>Name:</label>
						<?= $pageDetails->page; ?>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->

			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Public or Private?</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<label>Private:</label>
						<?php
						$checked = ($pageDetails->private == 1)? ' checked' : ''; ?>
						<input type='checkbox' name='private' id='private' value='Yes'<?=$checked;?>>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->

			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Remove Access</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<?php
						//Display list of groups with access
						$groupIds = [];
						foreach($pageGroups as $group) {
							$groupIds[] = $group->group_id;
						}
						foreach ($groupData as $v1) {
							if (in_array($v1->id,$groupIds)) { ?>
							<input type='checkbox' name='removeGroup[]' id='removeGroup[]' value='<?=$v1->id;?>'> <?=$v1->name;?><br/>
							<?php }} ?>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->

			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Add Access</strong></div>
					<div class="panel-body">
						<div class="form-group">
						<?php
						//Display list of groups without access
						foreach ($groupData as $v1) {
						if(!in_array($v1->id,$groupIds)) { ?>
						<input type='checkbox' name='addGroup[]' id='addGroup[]' value='<?=$v1->id;?>'> <?=$v1->name;?><br/>
						<?php }} ?>
						</div>
					</div>
				</div><!-- /panel -->
			</div><!-- /.col -->
			</div><!-- /.row -->

			<input type="hidden" name="csrf" value="<?=Token::generate();?>" >
			<p><input class='btn btn-primary' type='submit' value='Update' class='submit' /></p>
			</form>
        </div>
    </div>

    <!-- /.row -->
    <!-- footers -->
<?php require_once pathFinder('includes/page_footer.php'); // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once pathFinder('includes/html_footer.php'); // currently just the closing /body and /html ?>
