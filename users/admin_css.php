<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com
*/

require_once 'init.php';
require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/header.php';

/*
Secures the page...required for page permission management
*/
if (!securePage($_SERVER['PHP_SELF'])){die();}
checkToken();

if(!empty($_POST['css'])){
	if(configGet('css_sample') != Input::get('css_sample')) {
		$css_sample = Input::get('css_sample');
		$fields=array('css_sample'=>$css_sample);
		$db->update('settings',1,$fields);
	}

	if(configGet('css1') != Input::get('css1')) {
		$css1 = Input::get('css1');
		$fields=array('css1'=>$css1);
		$db->update('settings',1,$fields);
	}
	if(configGet('css2') != Input::get('css2')) {
		$css2 = Input::get('css2');
		$fields=array('css2'=>$css2);
		$db->update('settings',1,$fields);
	}

	if(configGet('css3') != Input::get('css3')) {
		$css3 = Input::get('css3');
		$fields=array('css3'=>$css3);
		$db->update('settings',1,$fields);
	}
	Redirect::to('admin_css.php');
}

?>
<div class="row"> <!-- row for Users, Permissions, Pages, Email settings panels -->
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=configGet('version')?></h1>
	<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
	<div class="col-xs-12"><!-- CSS Settings Column -->
		<form action="admin_css.php" name="css" method="post">
		<!-- Test CSS Settings -->
		<h2>Sitewide CSS</h2>

		<div class="form-group">
			<label for="css_sample">Show CSS Samples</label>
			<select id="css_sample" class="form-control" name="css_sample">
				<option value="1" <?php if(configGet('css_sample')==1) echo 'selected="selected"'; ?> >Enabled</option>
				<option value="0" <?php if(configGet('css_sample')==0) echo 'selected="selected"'; ?> >Disabled</option>
			</select>
		</div>

		<div class="form-group">
			<label for="css1">Primary Color Scheme (Loaded 1st)</label>
			<select class="form-control" name="css1" id="css1" >
				<option selected="selected"><?=configGet('css1')?></option>
				<?php
				$css_userspice=glob('../users/css/color_schemes/*.css');
				$css_custom=glob('../usersc/css/color_schemes/*.css');
				foreach(array_merge($css_userspice,$css_custom) as $filename){
					$filename=str_replace('../','',$filename);
					echo "<option value='".$filename."'>".$filename."";
				}
				?>
			</select>
		</div>

		<div class="form-group">
			<label for="css2">Secondary CSS (Loaded 2nd)</label>
			<select class="form-control" name="css2" id="css2">
				<option selected="selected"><?=configGet('css2')?></option>
				<?php
				$css_userspice=glob('../users/css/*.css');
				$css_custom=glob('../usersc/css/*.css');
				foreach(array_merge($css_userspice,$css_custom) as $filename){
					$filename=str_replace('../','',$filename);
					echo "<option value='".$filename."'>".$filename."";
				}
				?>
			</select>
		</div>

		<div class="form-group">
			<label for="css3">Custom CSS (Loaded 3rd)</label>
			<select class="form-control" name="css3" id="css3">
				<option selected="selected"><?=configGet('css3')?></option>
				<?php
				$css_userspice=glob('../users/css/*.css');
				$css_custom=glob('../usersc/css/*.css');
				foreach(array_merge($css_userspice,$css_custom) as $filename){
					$filename=str_replace('../','',$filename);
					echo "<option value='".$filename."'>".$filename."";
				}
				?>
			</select>
		</div>
		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<p><input class='btn btn-large btn-primary' type='submit' name="css" value='Save CSS Settings'/></p>
		</form>
	</div> <!-- /col1/3 -->
</div> <!-- /row -->

<?php if (configGet('css_sample')){?>
<div class="row">

	<div class="col-xs-12 text-center">
	<h2>Bootstrap Class Examples</h2>
	<hr />
	<button type="button" name="button" class="btn btn-primary">primary</button>
	<button type="button" name="button" class="btn btn-info">info</button>
	<button type="button" name="button" class="btn btn-warning">warning</button>
	<button type="button" name="button" class="btn btn-danger">danger</button>
	<button type="button" name="button" class="btn btn-success">success</button>
	<button type="button" name="button" class="btn btn-default">default</button>
	<hr />
	<div class="jumbotron"><h1>Jumbotron</h1></div>
	<div class="well"><p>well</p></div>
	<h1>This is H1</h1>
	<h2>This is H2</h2>
	<h3>This is H3</h3>
	<h4>This is H4</h4>
	<h5>This is H5</h5>
	<h6>This is H6</h6>
	<p>This is paragraph</p>
	<a href="#">This is a link</a><br><br>
	</div>
</div>
<?php } ?>


<!-- footers -->
<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
