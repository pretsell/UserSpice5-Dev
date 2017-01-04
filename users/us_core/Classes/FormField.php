<?php
/*
 * Class inheritance related to Form Fields:
 * The rule with UserSpice is "Do NOT modify files under us_core/ - make the change
 * under local/ instead." For these Form Fields the way to do that is to use the
 * classes defined in local/Classes/FormFieldTypes.php and to make modifications only
 * to that file and local/Classes/FormField.php.
 *
 * us_core/Classes/FormField.php (this) defines class "US_FormField" which is just
 * the parent class. This class is abstract. Do not modify this file.
 *
 * local/Classes/FormField.php in turn defines class "FormField" which inherits from
 * class "US_FormField". This class is abstract. Feel free to make changes to
 * local/Classes/FormField.php.
 *
 * us_core/Classes/FormFieldTypes.php in turn defines several classes such as
 * "US_FormField_Text", "US_FormField_Button", "US_FormField_Hidden", etc. which
 * inherit from class "FormField". These are abstract. Do not modify this file.
 *
 * local/Classes/FormFieldTypes.php in turn defines several classes such as
 * "FormField_Text", "FormField_Button", "FormField_Hidden", etc. which inherit
 * from the classes mentioned above which are named the same but with a "US_" prefix.
 * THESE ARE THE CLASSES YOU SHOULD USE IN YOUR CODE TO DEFINE FORM FIELDS!
 * Feel free to modify local/Classes/FormFieldTypes.php.
 */
abstract class US_FormField extends Element {
    protected $_validateObject=null,
        $_fieldName=null,
        $_dbFieldName=null,
        $_fieldId='',
        $_fieldLabel='',
        $_fieldPlaceholder=null,
        $_fieldValue=null,
        $_fieldNewValue=null,
        $_fieldType=null, // should be set by inheriting classes
        $_isDBField=true; // whether this is a field in the DB
    public $repEmptyAlternateReplacesAll = true;
    public
        $HTML_Pre = '
            <div class="{DIV_CLASS}">
              <label class="{LABEL_CLASS}" for="{FIELD_ID}">{LABEL_TEXT}
              <span class="{HINT_CLASS}" title="{HINT_TEXT}"></span></label>
              <br />',
        $HTML_Input = '
              <input class="{INPUT_CLASS}" type="{TYPE}" id="{FIELD_ID}" '
            .'name="{FIELD_NAME}" placeholder="{PLACEHOLDER}" value="{VALUE}" '
            .'{REQUIRED_ATTRIB} {EXTRA_ATTRIB}>',
        $HTML_Post = '
            </div> <!-- {DIV_CLASS} -->',
        $HTML_Script = '',
        $elementList = ['Pre', 'Input', 'Post'];
    # Commented-out values below are added just-in-time prior to replacement
    # (see self::getHTML()) Note that some replacement macros may be set/used
    # in self::setRepeatValues() as well.
    public
        $MACRO_Div_Class = 'form-group',
        $MACRO_Label_Class = 'control-label',
        $MACRO_Label_Text = '',
        $MACRO_Input_Class = 'form-control',
        $MACRO_Required_Class = 'fa fa-asterisk',
        $MACRO_Hint_Class = '',
        $MACRO_Hint_Class_Not_Required = 'fa fa-info-circle',
        $MACRO_Hint_Class_Required = 'fa fa-asterisk',
        $MACRO_Hint_Text = '',
        $MACRO_TH_Class = '',
        $MACRO_Placeholder = '',
        $MACRO_Extra_Attrib = '',
        $MACRO_Value = '';

    public function __construct($opts=[]) {
        global $T;
        if ($fn = @$opts['dbfield']) {
            $db = DB::getInstance();
            $field_def = $db->query("SELECT * FROM $T[field_defs] WHERE name = ?", [$fn])->first(true);
            $dbFieldnm = $fn;
            if ($field_def) {
                $fn = $field_def['alias'];
            }
            $this->setDBFieldName($dbFieldnm);
            unset($opts['dbfield']); // don't need anymore
        } else {
            $fn = @$opts['field']; // grab it if it's there
            unset($opts['field']); // don't need anymore
            $field_def = []; // no field-def to work with
        }
        if ($fn) {
            $this->setFieldName($fn);
        }
        if (is_null($this->getPlaceholder())) {
            $this->setPlaceholder($this->getFieldLabel());
        }
        parent::__construct($opts);
        # Now handle what we found in $field_def, but don't let
        # values there override what was passed in $opts
        if (isset($opts['display']) || isset($opts['display_lang'])) {
            unset($field_def['display']);
            unset($field_def['display_lang']);
        }
        $this->handleOpts(array_diff_key((array)$field_def, $opts));
    }

    public function handle1Opt($name, $val) {
        switch(strtolower($name)) {
            case 'display_lang':
                $val = lang($val);
                # NOTE: No break - falling through to 'display' with $val set
            case 'display':
                # NOTE: We could be falling through from above with no break
                $this->setFieldLabel($val);
                $this->setMacro('Label_Text', $val);
                return true;
                break;
            case 'value':
                $this->setFieldValue($val);
                return true;
                break;
            case 'new_valid':
            case 'new_validate':
                if (!isset($val['display']) && ($d = $this->getFieldLabel())) {
                    $val['display'] = $d;
                }
                $args = [$this->_dbFieldName => $val];
                $val = new Validate($args);
                # NOTE: No break - falling through to 'valid' with $val set
            case 'valid':
            case 'validate':
                # NOTE: We could be falling through from above with no break
                $this->setValidator($val);
                return true;
                break;
            case 'is_dbfield':
            case 'is_db_field':
            case 'isdbfield':
                $this->setIsDBField($val);
                return true;
                break;
            case 'placeholder':
                $this->setPlaceholder($val);
                return true;
                break;
            case 'extra':
                $this->setMacro('Extra_Attrib', $val);
                return true;
                break;
            case 'field_id':
                $this->setFieldId($val);
                return true;
                break;
        }
        return parent::handle1Opt($name, $val);
    }

    public function getMacros($s, $opts) {
        $this->MACRO_Type = $this->getFieldType();
        $this->MACRO_Field_Name = $this->getFieldName();
        $this->MACRO_Field_ID = $this->getFieldId();
        $this->MACRO_Label_Text = $this->getFieldLabel();
        $this->MACRO_Value = $this->getFieldValue();
        $this->MACRO_Required_Attrib = ($this->getRequired() ? 'required' : '');
        $this->MACRO_Hint_Class = $this->getHintClass();
        if (!$this->MACRO_Hint_Text && $this->hasValidation()) {
            $this->MACRO_Hint_Text = $this->getValidator()->describe($this->_fieldName);
        }
        return parent::getMacros($s, $opts);
    }

    public function setRepData($val) {
        # convert from object ($data->id, $data->name) to associative array
        # ($data['id'], $data['name']) if needed
        if (sizeof($val)>0 && is_object($val[0])) {
            $tmp = [];
            foreach ((array)$val as $k=>$o) {
                $tmp[$k] = (array)$o;
            }
            $this->repData = $tmp;
        } else {
            $this->repData = $val;
        }
        #dbg("setRepData(): values follow (class=".get_class($this).")");
        #var_dump($this->repData);
    }
    public function describeValidation() {
        return $this->getValidator()->describe($this->_fieldName);
    }
    public function getHintClass() {
        if ($this->getRequired()) {
            return $this->MACRO_Hint_Class_Required;
        } else {
            return $this->MACRO_Hint_Class_Not_Required;
        }
    }

    public function isChanged() {
        return ($this->_fieldNewValue != $this->_fieldValue);
    }
	public function getPlaceholder(){
		return $this->MACRO_Placeholder;
	}
	public function setPlaceholder($placeholder){
		$this->MACRO_Placeholder = $placeholder;
	}
    # Does the validation for this field say it is a required field?
	public function getRequired() {
        if ($valid = $this->getValidator()) {
            return $valid->getRequired($this->getFieldName());
        } else {
            return false;
        }
	}
	public function setRequired($v){
        $this->debug(2, "::setRequired($v) - Entering");
        if ($valid = $this->getValidator()) {
            $valid->setRequired($this->getFieldName(), $v);
        } else {
            throw new Exception("No validation. Cannot set `required` for field {$this->_fieldName}.");
        }
	}
    # <input ... id="THIS-IS-FIELD-ID" ...>
    public function setFieldId($id) {
        $this->_fieldId = $id;
    }
    public function getFieldId() {
        # Often developers will not specify the ID since they will just
        # want it to be the same as the field name. Thus the short-cut.
        if (!empty($this->_fieldId)) {
            return $this->_fieldId;
        } else {
            return $this->getFieldName();
        }
    }
    public function setFieldLabel($label) {
        $this->_fieldLabel = $label;
    }
    public function getFieldLabel() {
        return $this->_fieldLabel;
    }
    public function setIsDBField($isdb) {
        $this->_isDBField = $isdb;
    }
    public function getIsDBField() {
        return $this->_isDBField;
    }
    public function setDBFieldName($fn) {
        $this->_dbFieldName = $fn;
    }
    public function getDBFieldName() {
        return $this->_dbFieldName;
    }
    public function setFieldName($fn) {
        $this->_fieldName = $fn;
    }
    public function getFieldName() {
        return $this->_fieldName;
    }
    public function setFieldType($type) {
        $this->_fieldType = $type;
    }
    public function getFieldType() {
        return $this->_fieldType;
    }
    public function setFieldValue($value) {
        $this->_fieldValue = $value;
    }
    public function getFieldValue() {
        return $this->_fieldValue;
    }
    public function getNewValue() {
        return $this->_fieldNewValue;
    }
    public function setNewValue($val) {
        if ($this->getIsDBField()) {
            $this->_fieldNewValue = Input::sanitize($val);
        }
    }

    # methods related to validation
    public function setValidator($v) {
        $this->_validateObject = $v;
    }
    public function hasValidation() {
        return (boolean)$this->_validateObject;
    }
    public function getValidator($createIfNeeded=true) {
        if ($createIfNeeded && !$this->_validateObject) {
            $this->setValidator(new Validate());
        }
        return $this->_validateObject;
    }

    #
    # these methods are simply "pass-through" to the validate object
    #
    public function dataIsValid($data) {
        if ($this->hasValidation()) {
            if (!$data) {
                $data = $this->_fieldNewValue;
            }
            return $this->getValidator()->check($data)->passed();
        } else {
            return true; // if no validation then it cannot fail
        }
    }
    public function stackErrorMessages($errors) {
        if ($this->hasValidation()) {
            return $this->getValidator()->stackErrorMessages($errors);
        } else {
            return $errors;
        }
    }

    public function getHTMLScripts() {
        #dbg("::getHTMLScripts - ".$this->HTML_Script);
        return $this->HTML_Script;
    }
    // if an inheriting class needs to adjust the snippets
    // they can do it by setting any of ...
    // ... setting HTMLPre, HTMLInput, HTMLPost directly
    // ... or by overriding this function to do something else
    public function fixSnippets() {
    }
}
