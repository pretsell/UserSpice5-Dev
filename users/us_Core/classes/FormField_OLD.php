<?php
class FormFieldx {
	private $_label, $_id, $_name, $_element, $_elementAndType, $_type, $_labelClass, $_objectWrapperClass, $_elementClass, $_placeholder, $_helpText, $_btnClassString='btn ', $_inputOptions=array(), $_currentValue, $_html, $_required=false, $_autofocus=false;
	private $_validateObject, $_validateArray, $_validateErrors;

	public function __construct($label='New Field',$id='newfield',$elementAndType='input_text'){
		$this->_label=$label;
		$this->_id=$id;
		$this->_name=$id;
		$this->_elementAndType=$elementAndType;

		$this->_labelClass.=' control-label';
		$this->_objectWrapperClass.=' ';
		$this->_elementClass.=' form-control';
		$this->_currentValue='';
        $this->type='text';

		/*
		$type = text,password,submit,reset,radio,checkbox,button,color,date,datetime,
						datetime-local,email,month,number,range,search,tel,time,url,week
		$elementAndType values: input_text, input_password, input_radio, input_radio_inline, input_checkbox, input_checkbox_inline, input_select, input_textarea, button_submit, button_reset, button_file
		*/
	}

	public function setInputOptions($inputOptions=array()){
		$this->_inputOptions = $inputOptions;
	}

	public function setBtnClassString($btnClassString){
		$this->_btnClassString .= $btnClassString;
	}

	public function setCurrentValue($currentValue=''){
		/*
		For a text input or text area, this should set to a string for the current value 'blah blah'
		For a radio input, this should be set to a string with the 0-based index set to the selected value e.g. '2'
		For a checkbox input, this should be set to an array of true/false 1/0 e.g. ['1','0']
		*/
		$this->_currentValue = $currentValue;
	}

	public function setValidateArray($specs){
		$this->_validateArray=$specs;
	}

	public function getValidateArray(){
		return $this->_validateArray;
	}


	public function required(){
		$this->_required = true;
	}

	public function autofocus(){
		$this->_autofocus = true;
	}

	public function getName(){
		return $this->_name;
	}

	public function getId(){
		return $this->_id;
	}

	public function outputCode(){

		switch($this->_elementAndType){
			case 'input_text':
				/*
				Desired HTML output
				<div class="form-group">
					<label class="control-label" for="textinput">Text Input</label>
					<div class="">
					<input id="textinput" name="textinput" type="text" placeholder="placeholder" class="form-control input-md">
					<span class="help-block">help</span>
					</div>
				</div>
				*/
				$this->_html='';
				$this->_html.='<div class="form-group">';
				$this->_html.='<label class="'.$this->_labelClass.'" for="'.$this->_id.'">'.$this->_label.'</label>';
				$this->_html.='<div class="'.$this->_objectWrapperClass.'">';
				$this->_html.='<input class="'.$this->_elementClass.'" id="'.$this->_id.'" name="'.$this->_id.'" type="text" placeholder="'.$this->_placeholder.'" value="'.$this->_currentValue.'" '.(($this->_required) ? "required" : "").' '.(($this->_autofocus) ? "autofocus" : "").'>';
				$this->_html.='<span class="help-block">'.$this->_helpText.'</span>';
				$this->_html.='</div>';
				$this->_html.='</div>';
				break;

			case 'input_password':
				/*
				Desired HTML output
				<div class="form-group">
					<label class="control-label" for="textinput">Text Input</label>
					<div class="">
					<input id="textinput" name="textinput" type="password" placeholder="placeholder" class="form-control input-md">
					<span class="help-block">help</span>
					</div>
				</div>
				*/
				$this->_html='';
				$this->_html.='<div class="form-group">';
				$this->_html.='<label class="'.$this->_labelClass.'" for="'.$this->_id.'">'.$this->_label.'</label>';
				$this->_html.='<div class="'.$this->_objectWrapperClass.'">';
				$this->_html.='<input class="'.$this->_elementClass.'" id="'.$this->_id.'" name="'.$this->_id.'" type="password" placeholder="'.$this->_placeholder.'" '.(($this->_required) ? "required" : "").' '.(($this->_autofocus) ? "autofocus" : "").'>';
				$this->_html.='<span class="help-block">'.$this->_helpText.'</span>';
				$this->_html.='</div>';
				$this->_html.='</div>';
				break;

			case 'input_radio':
				/*
				Desired HTML output
				<!-- Multiple Radios -->
				<div class="form-group">
					<label class="control-label" for="radios">Multiple Radios</label>
					<div class="">
					<div class="radio">
						<label for="radios-0">
							<input type="radio" name="radios" id="radios-0" value="1">
							Option one
						</label>
					</div> <!-- radio -->
					<div class="radio">
						<label for="radios-1">
							<input type="radio" name="radios" id="radios-1" value="2">
							Option two
						</label>
					</div> <!-- radio -->
					</div> <!-- objectWrapperClass -->
				</div>	<!-- form-group -->
				*/
				$this->_html='';
				$this->_html.='<div class="form-group">';
				$this->_html.='<label class="'.$this->_labelClass.'" for="'.$this->_id.'">'.$this->_label.'</label>';
				$this->_html.='<div class="'.$this->_objectWrapperClass.'">';

				$i=0;
				foreach($this->_inputOptions as $key=>$value){
					$this->_html.='<div class="radio">';
					$this->_html.='<label for="'.$this->_id.'-'.$i.'">';
					$this->_html.='<input type="radio" name="'.$this->_id.'" id="'.$this->_id.'-'.$i.'" value="'.$value.'" '.(($i==$this->_currentValue)? 'checked':'').'>';
					$this->_html.=''.$key.'';
					$this->_html.='</label>';
					$this->_html.='</div>';

					$i++;
				}
				$this->_html.='</div>';
				$this->_html.='</div>';

				break;

			case 'input_radio_inline':
				/*
				Desired HTML output

				<div class="form-group">
					<label class=" control-label" for="radios">Inline Radios</label>
					<div class="">
						<label class="radio-inline" for="radios-0">
							<input type="radio" name="radios" id="radios-0" value="1">
							1
						</label>
						<label class="radio-inline" for="radios-1">
							<input type="radio" name="radios" id="radios-1" value="2">
							2
						</label>
					</div> <!-- objectWrapperClass -->
				</div> <!-- form-group -->


				*/
				$this->_html='';
				$this->_html.='<div class="form-group">';
				$this->_html.='<label class="'.$this->_labelClass.'" for="'.$this->_id.'">'.$this->_label.'</label>';
				$this->_html.='<div class="'.$this->_objectWrapperClass.'">';

				$i=0;
				foreach($this->_inputOptions as $key=>$value){
					$this->_html.='<label class="radio-inline" for="'.$this->_id.'-'.$i.'">';
					$this->_html.='<input type="radio" name="'.$this->_id.'" id="'.$this->_id.'-'.$i.'" value="'.$value.'"'.(($i==$this->_currentValue)? 'checked':'').'>';
					$this->_html.=''.$key.'';
					$this->_html.='</label>';

					$i++;
				}
				$this->_html.='</div>';
				$this->_html.='</div>';

				break;


			case 'input_checkbox':
				/*
				Desired HTML output
				<div class="form-group">
					<label class="control-label" for="checkboxes">Multiple Checkboxes</label>
					<div class="">
					<div class="checkbox">
						<label for="checkboxes-0">
							<input type="checkbox" name="checkboxes" id="checkboxes-0" value="1">
							Option one
						</label>
					</div>
					<div class="checkbox">
						<label for="checkboxes-1">
							<input type="checkbox" name="checkboxes" id="checkboxes-1" value="2">
							Option two
						</label>
					</div>
					</div>
				</div>

				*/
				$this->_html='';
				$this->_html.='<div class="form-group">';
				$this->_html.='<label class="'.$this->_labelClass.'" for="'.$this->_id.'">'.$this->_label.'</label>';
				$this->_html.='<div class="'.$this->_objectWrapperClass.'">';

				$i=0;

				foreach($this->_inputOptions as $key=>$value){
					$this->_html.='<div class="checkbox">';
					$this->_html.='<label for="'.$this->_id.'-'.$i.'">';
					$this->_html.='<input type="checkbox" name="'.$this->_id.'" id="'.$this->_id.'-'.$i.'" value="'.$value.'" '.((is_array($this->_currentValue) && $this->_currentValue[$i])?'checked':'').'>';
					$this->_html.=''.$key.'';
					$this->_html.='</label>';
					$this->_html.='</div>';

					$i++;
				}
				$this->_html.='</div>';
				$this->_html.='</div>';

				break;

			case 'input_checkbox_inline':
				/*
				Desired HTML output

				<div class="form-group">
					<label class="control-label" for="checkboxes">Inline Checkboxes</label>
					<div class="">
						<label class="checkbox-inline" for="checkboxes-0">
							<input type="checkbox" name="checkboxes" id="checkboxes-0" value="1">
							1
						</label>
						<label class="checkbox-inline" for="checkboxes-1">
							<input type="checkbox" name="checkboxes" id="checkboxes-1" value="2">
							2
						</label>
					</div>  <!-- objectWrapperClass -->
				</div> <!-- form-group -->


				*/
				$this->_html='';
				$this->_html.='<div class="form-group">';
				$this->_html.='<label class="'.$this->_labelClass.'" for="'.$this->_id.'">'.$this->_label.'</label>';
				$this->_html.='<div class="'.$this->_objectWrapperClass.'">';

				$i=0;
				foreach($this->_inputOptions as $key=>$value){
					$this->_html.='<label class="checkbox-inline" for="'.$this->_id.'-'.$i.'">';
					$this->_html.='<input type="checkbox" name="'.$this->_id.'" id="'.$this->_id.'-'.$i.'" value="'.$value.'" '.((is_array($this->_currentValue) && $this->_currentValue[$i])?'checked':'').'>';
					$this->_html.=''.$key.'';
					$this->_html.='</label>';

					$i++;
				}
				$this->_html.='</div>';
				$this->_html.='</div>';

				break;

			case 'input_select':
				/*
				Desired HTML output
				<div class="form-group">
					<label class=" control-label" for="selectbasic">Select Basic</label>
					<div class="">
						<select id="selectbasic" name="selectbasic" class="form-control">
							<option value="1">Option one</option>
							<option value="2">Option two</option>
						</select>
					</div>
				</div>
				*/

				$this->_html='';
				$this->_html.='<div class="form-group">';
				$this->_html.='<label class="'.$this->_labelClass.'" for="'.$this->_id.'">'.$this->_label.'</label>';
				$this->_html.='<div class="'.$this->_objectWrapperClass.'">';
				$this->_html.='<select class="'.$this->_elementClass.'" name="'.$this->_id.'" id="'.$this->_id.'">';

				foreach($this->_inputOptions as $key=>$value){
					$this->_html.='<option value="'.$value.'">'.$key.'</option>';
				}
				$this->_html.='</select>';
				$this->_html.='</div>';
				$this->_html.='</div>';

				break;

			case 'input_textarea':
				/*
				Desired HTML output
				<div class="form-group">
					<label class=" control-label" for="textarea">Text Area</label>
					<div class="">
						<textarea class="form-control" id="textarea" name="textarea">default text</textarea>
					</div>
				</div>
				*/
				$this->_html='';
				$this->_html.='<div class="form-group">';
				$this->_html.='<label class="'.$this->_labelClass.'" for="'.$this->_id.'">'.$this->_label.'</label>';
				$this->_html.='<div class="'.$this->_objectWrapperClass.'">';
				$this->_html.='<textarea class="'.$this->_elementClass.'" id="'.$this->_id.'" name="'.$this->_id.'" '.(($this->_required) ? "required" : "").'>';
				$this->_html.=''.$this->_currentValue.'';
				$this->_html.='</textarea>';
				$this->_html.='</div>';
				$this->_html.='</div>';
				break;

			case 'button_submit':
				/*
				Desired HTML output
				<div class="form-group">
					<label class="control-label" for="singlebutton"></label>
					<div class="">
						<button id="singlebutton" name="singlebutton" class="btn btn-primary">Button</button>
					</div>
				</div>
				*/
				$this->_html='';
				//$this->_html.='<div class="form-group">';
				//$this->_html.='<label class="'.$this->_labelClass.'" for="'.$this->_id.'">'.$this->_label.'</label>';
				//$this->_html.='<div class="'.$this->_objectWrapperClass.'">';
				$this->_html.='<button type="submit" id="'.$this->_id.'" name="'.$this->_id.'" class="btn '.$this->_btnClassString.'">'.$this->_label.'</button>';
				//$this->_html.='</div>';
				//$this->_html.='</div>';
				break;

			case 'button_reset':
				/*
				Desired HTML output

				*/
				$this->_html='';
				//$this->_html.='<div class="form-group">';
				//$this->_html.='<label class="'.$this->_labelClass.'" for="'.$this->_id.'">'.$this->_label.'</label>';
				//$this->_html.='<div class="'.$this->_objectWrapperClass.'">';
				$this->_html.='<button type="reset" id="'.$this->_id.'" name="'.$this->_id.'" class="btn '.$this->_btnClassString.'">'.$this->_label.'</button>';
				//$this->_html.='</div>';
				//$this->_html.='</div>';
				break;

			default:
				$this->_html='This elementAndType is unknown. Please review valid types.';
				break;
		}
		echo $this->_html;
	}
}
