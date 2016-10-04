<?php
/*

*/
class Form {
	private $_name, $_actionPage, $_fieldCount, $_fields=array(), $_validateObject, $_formValid;

	public function __construct($fields){
		$this->_fields=$fields;
		$this->_fieldCount=sizeof($this->_fields);
	}
	
	public function setActionPage($actionPage){
		$this->_actionPage=$actionPage;
	}
	
	public function setName($name){
		$this->_name=$name;
	}
	
		//$validation->check($_POST,array(
		//  'username' => array('display' => 'Username','required' => true,'min' => 5,'max' => 35,'unique' => 'users',),
		//  'fname' => array('display' => 'First Name','required' => true,'min' => 2,'max' => 35,),
		//  'lname' => array('display' => 'Last Name','required' => true,'min' => 2,'max' => 35,),
		//  'email' => array('display' => 'Email','required' => true,'valid_email' => true,'unique' => 'users',),
		//  'password' => array('display' => 'Password','required' => true,'min' => 6,'max' => 25,),
		//  'confirm' => array('display' => 'Confirm Password','required' => true,'matches' => 'password',),
		//));		
	
	
	public function validate(){
		$this->_validateObject = new Validate();

		$items=[];
		$i=0;
		foreach ($this->_fields as $field){
			$items[$this->_fields[$i]->getId()]=$this->_fields[$i]->getValidateArray();
			$i++;
		}
		//dump($items);
		
		$this->_validateObject->check($_POST,$items);
		if($this->_validateObject->passed()){
			$this->_formValid=true;
		}else{
			$this->_formValid=false;
		}
		
		
	}
	
	public function getValidateErrors(){
		$validateErrors=$this->_validateObject->errors();
		$this->_validateObject=null;
		return $validateErrors;
	}
	
	public function setFields($fields){
		$this->_fields=$fields;
	}
	
	public function getFormValid(){
		return $this->_formValid;
	}
	
}
