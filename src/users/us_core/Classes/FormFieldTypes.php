<?php
/*
 * See the detailed description of the inheritance structure related to
 * the class "FormField" which is provided in the header comment in
 * us_core/Classes/FormField.php.
 *
 * In short,
 *    - do not change this file (it is under us_core/ - don't ever change
 *      anything under us_core/)
 *    - do not instantiate from these classes (they are abstract)
 *    - the classes you are looking for are in local/Classes/FormField.php
 *      (they are named like these but without the "US_" prefix)
 */


# To modify FormField_Button, find the definitions of FormField_ButtonAnchor,
# FormField_ButtonSubmit, etc. in local/Classes/FormFieldTypes.php
abstract class FormField_Button extends FormField {
    protected $_fieldType = "submit";
    protected $_isDBField = false; // more appropriate default for most buttons
    protected $_fieldValue = "pressed";
    public $elementList = ['Input'], // no Pre or Post
        $HTML_Input = '
            <button class="{INPUT_CLASS}" name="{FIELD_NAME}" value="{VALUE}" /><span class="{BUTTON_ICON}"></span> {LABEL_TEXT}</button>
            ',
        $MACRO_Button_Icon = '',
        $MACRO_Input_Class = 'btn btn-primary';
}
abstract class US_FormField_ButtonAnchor extends FormField_Button {
    protected $_fieldType = "button";
    public $HTML_Input = '
            <a href="{LINK}" class="{INPUT_CLASS}" type="{TYPE}"><span class="{BUTTON_ICON}"></span> {LABEL_TEXT}</a>
            ',
        $MACRO_Link = '';
}
abstract class US_FormField_ButtonSubmit extends FormField_Button {
}
abstract class US_FormField_ButtonDelete extends FormField_Button {
    public $MACRO_Input_Class = 'btn btn-primary btn-danger';
}

abstract class US_FormField_Checkbox extends FormField {
    protected $_fieldType = "checkbox";
	public $HTML_Pre =
            '<div class="{DIV_CLASS}">
            ',
        $HTML_Input =
    		'<input type="{TYPE}" name="{FIELD_NAME}" id="{FIELD_ID}" >
            ',
        $HTML_Post =
		    '<label class="{LABEL_CLASS}" for="{FIELD_ID}">{LABEL_TEXT}</label>
        	 </div> <!-- {DIV_CLASS} -->
             ';
}

abstract class US_FormField_Hidden extends FormField {
    protected $_fieldType = "hidden";
    public $elementList = ['Input'], // no Pre or Post
        $HTML_Input = '
            <input type="{TYPE}" name="{FIELD_NAME}" value="{VALUE}">
            ';
}

abstract class US_FormField_Password extends FormField {
    protected $_fieldType = "password";
}

abstract class US_FormField_Radio extends FormField {
    protected $_fieldType = "radio";
    public
        $HTML_Pre = '
            <div class="{DIV_CLASS}">
            <label class="{LABEL_CLASS}" for="{FIELD_ID}">{LABEL_TEXT}
            <span class="{HINT_CLASS}" title="{HINT_TEXT}"></span></label>
            ',
        $HTML_Input = '
            <div class="radio">
				<label for="{FIELD_ID}-{ID}">
					<input type="{TYPE}" name="{FIELD_NAME}" id="{FIELD_ID}-{ID}" class="{INPUT_CLASS}" value="{ID}">
					{OPTION_LABEL}
				</label>
			</div> <!-- radio -->
            ',
        $HTML_Post = '
            </div> <!-- {DIV_CLASS} -->
            ',
        $repElement = 'HTML_Input';
}

abstract class US_FormField_Recaptcha extends FormField {
    protected $_fieldType = "recaptcha"; // not used
    protected $_validateErrors = [];
    public $MACRO_Recaptcha_Class = 'g-recaptcha',
        $MACRO_Recaptcha_Public = '';
    public $HTML_Pre = '
            <div class="{DIV_CLASS}">
    		<label>{LABEL_TEXT}</label>
             ',
        $HTML_Input = '
            <div class="{RECAPTCHA_CLASS}" name="{RECAPTCHA_PUBLIC}"></div>
            ',
        $HTML_Post = '
            </div> <!-- {DIV_CLASS} -->
            ',
        $HTML_Script = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
    public function dataIsValid($data) {
		$remoteIp=$_SERVER["REMOTE_ADDR"];
		$gRecaptchaResponse=Input::sanitize($data['g-recaptcha-response']);
		$response = null;

		#require_once pathFinder('includes/recaptcha.config.php');
        $publickey = configGet('recaptcha_public'); // the Site key you received when you registered
        $privatekey = configGet('recaptcha_private'); // the Private key you received when you registered

		// check secret key
		$reCaptcha = new ReCaptcha(configGet('recaptcha_private'));

		// if submitted check response
		if ($gRecaptchaResponse) {
			$response = $reCaptcha->verifyResponse($remoteIp,$gRecaptchaResponse);
        }
		if ($response != null && $response->success) {
			return true;
		} else {
            $this->_validateErrors[] = lang('CAPTCHA_FAIL');
            return false;
		}
    }
    public function stackErrorMessages($errors) {
        return array_merge($errors, $this->_validateErrors);
    }
    public function hasValidation() {
        return true; // just a different kind of validation
    }
    public function getMacros($s, $opts) {
        $this->MACRO_Recaptcha_Public = configGet('recaptcha_public');
        return parent::getMacros($opts);
    }
}

abstract class US_FormField_Select extends FormField {
    protected $_fieldType = "select";
    public $MACRO_Selected = '';
    public $idField = 'id';
    public $repMacroAliases = ['{OPTION_VALUE}', '{OPTION_LABEL}'];
    public
        $HTML_Pre = '
            <div class="{DIV_CLASS}">
            <label class="{LABEL_CLASS}" for="{FIELD_ID}">{LABEL_TEXT}
            <span class="{HINT_CLASS}" title="{HINT_TEXT}"></span></label>
            <br />
            <select class="{INPUT_CLASS}" id="{FIELD_ID}" name="{FIELD_NAME}">
            ',
        $HTML_Input = '
            <option value="{ID}" {SELECTED}>{OPTION_LABEL}</option>
            ',
        $HTML_Post = '
            </select>
            </div> <!-- {DIV_CLASS} -->
            ',
        $repElement = 'HTML_Input';
    protected
        $placeholderRow = [],
        $selected = 'selected="selected"';
    public function handle1Opt($name, $val) {
        switch (strtolower($name)) {
            case 'placeholder_row':
                $this->setPlaceholderRow($val);
                return true;
                break;
            case 'idfield':
                $this->setIdField($val);
                return true;
                break;
        }
        return parent::handle1Opt($name, $val);
    }
    public function setPlaceholderRow($v) {
        $this->placeholderRow = $v;
    }
    public function getRepData() {
        if ($this->placeholderRow) {
            return array_merge([$this->placeholderRow], $this->repData);
        } else {
            return $this->repData;
        }
    }
    public function specialRowMacros(&$macros, $row) {
        parent::specialRowMacros($macros, $row);
        # Look for match, but be careful because null==0 in PHP and 0 is
        # a very normal value for id fields. But === doesn't suffice because
        # a "blank" (unset) value might be null in data but '' in select statement
        # for the first "Choose below" item
        $fv = $this->getFieldValue();
        #if (!@$row[$this->getIdField()]) var_dump($row);
        $rowVal = $row[$this->getIdField()];
        if (($fv === $rowVal) ||
                ($fv !== 0 && $rowVal !== 0 && $fv == $rowVal)) {
            $macros['{SELECTED}'] = $this->selected;
        } else {
            $macros['{SELECTED}'] = '';
        }
    }
    public function getIdField() {
        return $this->idField;
    }
    public function setIdField($val) {
        $this->idField = $val;
    }
}
abstract class US_FormField_Table extends FormField {
    protected $_fieldType = "table",
        $_dataFields = [],
        $_dataFieldLabels = [];
    public
        $MACRO_Table_Class = "table table-hover",
        $MACRO_TH_Row_Class = "",
        $MACRO_TH_Cell_Class = "",
        $MACRO_TD_Row_Class = "",
        $MACRO_TD_Cell_Class = "";
    public
        $HTML_Pre = '
            <div class="{DIV_CLASS}">
            <table class="{TABLE_CLASS}">
            <tr class="{TH_ROW_CLASS}">{TABLE_HEAD_CELLS}</tr>
            ',
        $HTML_Input = '
            <tr class="{TD_ROW_CLASS}">{TABLE_DATA_CELLS}</tr>
            ',
        $HTML_Post = '
            </table>
            </div> <!-- {DIV_CLASS} -->
            ',
        $HTML_Checkbox = '<input type="checkbox" name="{FIELD_NAME}[]" value="{ID}"/>',
        $repElement = 'HTML_Input';

    public function handle1Opt($name, $val) {
        switch (strtolower($name)) {
            case 'table_data_cells':
            case 'td_row':
                #dbg('setting table_data_cells');
                $this->HTML_Input = $this->processMacros(
                    ['{TABLE_DATA_CELLS}'=>$val,
                        '{CHECKBOX}' => $this->HTML_Checkbox],
                    $this->HTML_Input);
                #dbg('AFTER: HTML_Input='.htmlentities($this->HTML_Input));
                return true;
                break;
            case 'table_head_cells':
            case 'th_row':
                #dbg('setting table_head_cells');
                $this->HTML_Pre = $this->processMacros(
                    ['{TABLE_HEAD_CELLS}'=>$val], $this->HTML_Pre);
                #dbg("Setting Data Fields<br />\n");
                #dbg("_dataFields=".print_r($this->_dataFields,true));
                #dbg("_dataFieldLabels=".print_r($this->_dataFieldLabels,true));
                #dbg('AFTER: HTML_Pre='.htmlentities($this->HTML_Pre));
                return true;
                break;
        }
        return parent::handle1Opt($name, $val);
    }
}

# Tab Table-of-Contents
abstract class US_FormField_TabToC extends FormField {
    protected $_fieldType = "tabtoc"; // tabbed table of contents
    protected $tocType = "tab"; // "pill" is an alternative
    protected $tocClass = "nav nav-tabs";
    public $repElement = 'HTML_Input';
    public
        $HTML_Pre = '
            <ul class="{TAB_UL_CLASS}" id="myTab">
            ',
        $HTML_Input = '
            <li class="{TAB_ACTIVE}"><a href="#{TAB_ID}" data-toggle="{TOC_TYPE}">{TITLE}</a></li>
             ',
        $HTML_Post = '
             </ul>
             ';
    public function getTocType() {
        return $this->tocType;
    }
    public function setTocType($val) {
        $this->tocType = $val;
    }
    public function getMacros($s, $opts) {
        $this->MACRO_Tab_UL_Class = 'nav nav-'.$this->getTocType().'s'; # nav-tabs or nav-pills usually
        return parent::getMacros($s, $opts);
    }
    public function setRepData($opts=[]) {
        // typically getting an array from Form::getFields()
        $tmp = [];
        $active = 'active'; // first one active
        $toc_type = (isset($opts['toc-type']) ? $opts['toc-type'] : $this->tocType);
        foreach ($opts as $k=>$o) {
            #dbg('Class Name: '.get_class($o));
            $tmp[] = [
                'title'=>$o->getMacro('Form_Title'),
                'tab_id'=>$k,
                'tab_active'=>$active,
                'toc_type'=>$toc_type,
            ];
            $active = '';
        }
        $this->repData = $tmp;
    }
}

abstract class US_FormField_Text extends FormField {
    protected $_fieldType = "text";
}
