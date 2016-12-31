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

$errors = $successes = [];

if(isset($_GET['type'])){
	$type=Input::get('type');

	if($type=='forgot'){
		if(Input::exists('post')){
			$emailText=Input::get('emailText');
			$fields=array('forgot_password_template'=>$emailText);
			$db->update('settings','1',$fields);
		}else{
			$emailText=configGet('forgot_password_template');
		}
	}elseif($type=='verify'){
		if(Input::exists('post')){
			$emailText=Input::get('emailText');
			$fields=array('email_verify_template'=>$emailText);
			$db->update('settings','1',$fields);
		}else{
			$emailText=configGet('email_verify_template');
		}
	}else{
		/*
		Do nothing sinnce not valid type
		*/
	}
}

?>
<div class="row">
	<div class="col-xs-12">
	<h1 class="text-center">UserSpice Dashboard <?=configGet('version')?></h1>
	<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/admin_nav.php'; ?>
	</div>
</div> <!-- /.row -->

<div class="row">

<div class="col-xs-12">

	<h2><?=$type?> Template</h2>

	<p>At present email templates require the following "fields" to work properly</p>
	<ul>
	<li>{{fname}} The recepients first name</li>
	<li>{{url}} The URL they need to visit</li>
	<li>{{sitename}} The name of the site sending the email</li>
	</ul>

	<form name='updateEmailTemplate' action='admin_email_template.php?type=<?=$type?>' method='post'>
		<div class="form-group">
			<label>Email Text</label>
			<textarea rows="10" id="emailText" name="emailText" ><?=$emailText;?></textarea></p>
		</div>

		<input type="hidden" name="csrf" value="<?=Token::generate();?>" />

		<p class="text-center"><input class='btn btn-primary' type='submit' value='Update' class='submit' />
		<a class="btn btn-info" href="admin_email_template.php?type=<?=$type?>">Cancel</a></p>

	</form>

</div>
</div>

<!-- footers -->
<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- If you disable this script below you will get a standard textarea with NO WYSIWYG editor. That simple -->
<script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
<script>
tinymce.init({
  selector: '#emailText'
});
</script>

<?php require_once US_DOC_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
