<?php
require_once 'users/init.php';
require_once ABS_US_ROOT.US_URL_ROOT.'includes/header.php';
require_once ABS_US_ROOT.US_URL_ROOT.'includes/navigation.php';
?>

<div class="row">
<div class="col-xs-12">
<?php
$inputText=new FormField('New Text Input','newtext', 'input_text');
$inputText->setHelpText('This is the field help text');
$inputText->setPlaceholder('Enter input here');
$inputText->required();
$inputText->autofocus();
$inputText->setCurrentValue('Text Current Value');
$inputText->setLabelClass("col-md-4");
$inputText->setObjectWrapperClass("col-md-8");

$inputPassword=new FormField('New Password Input','newpassword', 'input_password');
$inputPassword->setHelpText('This is the field help text');
$inputPassword->setPlaceholder('Enter password here');
$inputPassword->required();
$inputPassword->setLabelClass("col-md-4");
$inputPassword->setObjectWrapperClass("col-md-8");

$inputRadio=new FormField('New Radio Input','newradio', 'input_radio');
$inputRadio->setInputOptions(['Radio 1'=>'1','Radio 2'=>'2']);
$inputRadio->setCurrentValue('-1');
$inputRadio->setLabelClass("col-md-4");
$inputRadio->setObjectWrapperClass("col-md-8");

$inputRadioInline=new FormField('New Radio Input','newradioin', 'input_radio_inline');
$inputRadioInline->setInputOptions(['Radio 1 Inline'=>'1','Radio 2 Inline'=>'2']);
$inputRadioInline->setCurrentValue('-1');
$inputRadioInline->setLabelClass("col-md-4");
$inputRadioInline->setObjectWrapperClass("col-md-8");

$inputCheckbox=new FormField('New Checkbox Input','newcheckbox', 'input_checkbox');
$inputCheckbox->setInputOptions(['Checkbox 1'=>'1','Checkbox 2'=>'2']);
$inputCheckbox->setCurrentValue(['1','0']);
$inputCheckbox->setLabelClass("col-md-4");
$inputCheckbox->setObjectWrapperClass("col-md-8");

$inputCheckboxInline=new FormField('New Checkbox Input','newcheckboxin', 'input_checkbox_inline');
$inputCheckboxInline->setInputOptions(['Checkbox 1 Inline'=>'1','Checkbox 2 Inline'=>'2']);
$inputCheckboxInline->setCurrentValue(['1','0']);
$inputCheckboxInline->setLabelClass("col-md-4");
$inputCheckboxInline->setObjectWrapperClass("col-md-8");

$inputSelect=new FormField('New Select Input','newselect', 'input_select');
$inputSelect->setInputOptions(['Select 1'=>'1','Select 2'=>'2','Select 3'=>'3','Select 4'=>'4']);
$inputSelect->setLabelClass("col-md-4");
$inputSelect->setObjectWrapperClass("col-md-8");

$inputTextarea=new FormField('New Textarea','newtextarea', 'input_textarea');
$inputTextarea->required();
$inputTextarea->setCurrentValue('Textarea Current Value');
$inputTextarea->setLabelClass("col-md-4");
$inputTextarea->setObjectWrapperClass("col-md-8");

$buttonSubmit=new FormField('New Button Submit','newbtnsubmit', 'button_submit');
$buttonSubmit->setCurrentValue('Submit');
$buttonSubmit->setBtnClassString(' btn-primary');

$buttonReset=new FormField('New Button Reset','newbtnreset', 'button_reset');
$buttonReset->setCurrentValue('Button Reset Text');
$buttonReset->setBtnClassString(' btn-primary');

?>


<form class="form-horizontal" action="form.php" method="post">

<!-- Class generated -->

<?=$inputText->outputCode()?>
<?//=$inputPassword->outputCode()?>
<?=$inputRadio->outputCode()?>
<?//=$inputRadioInline->outputCode()?>
<?=$inputCheckbox->outputCode()?>
<?//=$inputCheckboxInline->outputCode()?>
<?=$inputSelect->outputCode()?>
<?=$inputTextarea->outputCode()?>
<?=$buttonSubmit->outputCode()?>
<?//=$buttonReset->outputCode()?>

</form>

</div>
</div>

<?php
require_once ABS_US_ROOT.US_URL_ROOT.'includes/footer.php';
?>