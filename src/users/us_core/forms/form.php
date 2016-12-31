<?php
require_once 'users/init.php';
require_once US_DOC_ROOT.US_URL_ROOT.'includes/header.php';
require_once US_DOC_ROOT.US_URL_ROOT.'includes/navigation.php';

$errors = $successes = [];
$formValid=false;

checkToken();

/*
Build Form
*/
if (isset($_SESSION['formFields']) && isset($_SESSION['form'])) {
	$formFields=$_SESSION['formFields'];
	$form=$_SESSION['form'];
	unset($_SESSION['formFields']);
	unset($_SESSION['form']);

	$i=0;
	foreach($formFields as $formField) {
		$formFields[$i]->setCurrentValue(Input::get($formFields[$i]->getId()));
		$i++;
	}
	$form->setFields($formFields);
} else {
	/*
	Input from custom form
	*/
	require_once 'formdef.php';
	$_SESSION['formFields']=$formFields;
	$_SESSION['form']=$form;
}

if (isset($_POST['submit'])) {
	/*
	Form has been submitted with button submit
	*/
	$form->validate();
	foreach ($form->getValidateErrors() as $error) {
		$errors[]=$error;
	}

	if (!$form->getFormValid()) {
		/*
		This happens if the form processing failed, so the session variable is set again
		*/
		$i=0;
		foreach($formFields as $formField) {
			$formFields[$i]->setCurrentValue(Input::get($formFields[$i]->getId()));

			$i++;
		}
		$form->setFields($formFields);

		$_SESSION['formFields']=$formFields;
		$_SESSION['form']=$form;

	}
}
?>

<div class="row">
<div class="col-xs-12">
<h2>Errors</h2>
<?=display_errors($errors);?>
<h2>Successes</h2>
<?=display_successes($successes);?>

<form class="" action="form.php" method="post">
<?php
foreach($formFields as $formField) {
	$formField->outputCode();
}
?>
<input type="hidden" value="<?=Token::generate();?>" name="csrf">
</form>

</div>
</div>

<?php
require_once US_DOC_ROOT.US_URL_ROOT.'includes/footer.php';
?>
