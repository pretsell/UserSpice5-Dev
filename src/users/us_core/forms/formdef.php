<?php
$inputText1=new FormField('Text1','text1', 'input_text');
$inputText1->setValidateArray(array('display' => 'Text1 String','min' => 5,'required' => true));

$inputText2=new FormField('Text2','text2', 'input_text');
$inputText2->setValidateArray(array('display' => 'Text2 String','max' => 10,'required' => true));

$buttonSubmit=new FormField('Submit','submit', 'button_submit');
$buttonSubmit->setValidateArray(array('display' => 'Button','min' => 0));

$formFields=[$inputText1,$inputText2,$buttonSubmit];

$form=New Form($formFields);
$form->setActionPage('form.php');
?>
	