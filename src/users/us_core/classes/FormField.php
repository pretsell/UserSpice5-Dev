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
abstract class US_FormField {
    protected $_validateObject=null,
        $_fieldName=null,
        $_fieldId='',
        $_fieldLabel='',
        $_fieldPlaceholder=null,
        $_fieldValue=null,
        $_fieldNewValue=null,
        $_fieldType=null, // should be set by inheriting classes
        $_deleteMe=false,
        $_isDBField=true, // whether this is a field in the DB
        $_hintClass_notRequired = 'fa fa-info-circle',
        $_hintClass_required = 'fa fa-asterisk';
    public
        $HTMLPre =
            '<div class="{DIV-CLASS}">
             <label class="{LABEL-CLASS}" for="{FIELD-ID}">{LABEL-TEXT}
             <span class="{HINT-CLASS}" title="{HINT-TEXT}"></span></label>
             <br />',
        $HTMLInput =
            '<input class="{INPUT-CLASS}" type="{TYPE}" id="{FIELD-ID}" '
            .'name="{FIELD-NAME}" placeholder="{PLACEHOLDER}" value="{VALUE}" '
            .'{REQUIRED-ATTRIB} {EXTRA-ATTRIB}>',
        $HTMLPost =
            '<br />
             </div> <!-- {DIV-CLASS} -->',
        $HTMLScript = '';
    # Commented-out values below are added just-in-time prior to replacement
    # (see self::getHTML()) Note that some replacement macros may be set/used
    # in self::setRepeatValues() as well.
    protected $_macros = [
            '{DIV-CLASS}'  => 'form-group',
            '{LABEL-CLASS}'=> 'control-label',
            '{REQUIRED-CLASS}' => 'fa fa-asterisk',
            '{HINT-CLASS}' => 'fa fa-info-circle',
            '{INPUT-CLASS}'=> 'form-control',
            '{TH-CLASS}'    => '',
            '{PLACEHOLDER}'=> '',
        ];

    public function __construct($fn, $opts=[]) {
        global $T;
        $this->fixSnippets(); // this allows inheriting classes to adjust data
        $db = DB::getInstance();
        $field_def = $db->query("SELECT * FROM $T[field_defs] WHERE name = ?", [$fn])->first(true);
        $dbFieldnm = $fn;
        if ($field_def) {
            $fn = $field_def['alias'];
            $opts = array_merge($field_def, $opts);
        }
        $this->setFieldName($fn);
        $this->handleOpts($dbFieldnm, $opts);
        if (is_null($this->getPlaceholder())) {
            $this->setPlaceholder($this->getFieldLabel());
        }
    }

    public function handleOpts($dbFieldnm, $opts) {
        foreach ($opts as $k=>$v) {
            switch(strtolower($k)) {
                case 'display_lang':
                    $v = lang($v);
                    # NOTE: No break - falling through to 'label' with $v set
                case 'label':
                    # NOTE: We could be falling through from above with no break
                    $this->setFieldLabel($v);
                    break;
                case 'value':
                    $this->setFieldValue($v);
                    break;
                case 'new_valid':
                case 'new_validate':
                    $args = [$dbFieldnm => $v];
                    $v = new Validate($args);
                    # NOTE: No break - falling through to 'valid' with $v set
                case 'valid':
                case 'validate':
                    # NOTE: We could be falling through from above with no break
                    $this->setValidator($v);
                    break;
                case 'keep_if':
                case 'keepif':
                    $val = !$val;
                    # NOTE: No break - falling through to 'deleteif'
                case 'delete_if':
                case 'deleteif':
                    # NOTE: We could be falling through from above with no break
                    $this->setDeleteMe($val);
                    break;
                case 'is_dbfield':
                case 'is_db_field':
                case 'isdbfield':
                    $this->setIsDBField($v);
                    break;
                case 'placeholder':
                    $this->setPlaceholder($v);
                    break;
                case 'extra':
                    $this->setReplace('{EXTRA-ATTRIB}', $v);
                    break;
                default:
                    if (preg_match('/^\{.*\}$/', $k)) {
                        $this->setReplace(strtoupper($k), $v);
                        #var_dump($this->_macros);
                    } else {
                        if (is_string($v)) {
                            $this->setReplace('{'.strtoupper($k).'}', $v);
                        }
                    }
                    #dbg("__construct: _macros=<pre>".print_r($this->_macros,true)."</pre><br />");
                    // else ... don't do anything - may have come from extra DB columns
            }
        }
    }

    # $opts is a hash which can have the following indexed values:
    #  'replaces' => ['{search}'=>'replace',...]
    public function getHTML($opts=[]) {
        # Start by calculating $this->HTMLInput.
        $html = $this->getHTMLElements($opts);
        # Now we will calculate an array of macros for search/replace.
        # Static values are already in $this->_macros but others have
        # to be set "just in time"...
        $justInTimeRepl = [
                    '{TYPE}'           => $this->getFieldType(),
                    '{FIELD-NAME}'     => $this->getFieldName(),
                    '{FIELD-ID}'       => $this->getFieldId(),
                    '{LABEL-TEXT}'     => $this->getFieldLabel(),
                    '{PLACEHOLDER}'    => $this->getPlaceholder(),
                    '{VALUE}'          => $this->getFieldValue(),
                    '{REQUIRED-ATTRIB}'=> ($this->getRequired() ? 'required' : ''),
                    '{HINT-CLASS}'     => $this->getHintClass(),
        ];
        $this->jitMacros($justInTimeRepl);
        $repl = array_merge($this->_macros, $justInTimeRepl, (array)@$opts['replaces']);
        # since this is slightly "expensive" we won't evaluate unless it is needed
        if (!isset($repl['{HINT-TEXT}']) && $this->getValidator()) {
            $repl['{HINT-TEXT}'] = $this->getValidator()->describe($this->_fieldName);
        }
        $html = str_replace(array_keys($repl), array_values($repl), $html);
        return $html;
    }
    public function getHTMLElements($opts) {
        return $this->HTMLPre . $this->HTMLInput . $this->HTMLPost;
    }
    // these are overall just-in-time replacement macros
    public function jitMacros(&$macros) {
        // don't do anything by default - each field type may
        // have something to do...
    }

    public function describeValidation() {
        return $this->getValidator()->describe($this->_fieldName);
    }
    public function getHintClass() {
        if ($this->getRequired()) {
            return $this->_hintClass_required;
        } else {
            return $this->_hintClass_notRequired;
        }
    }

    public function isChanged() {
        return ($this->_fieldNewValue == $this->_fieldValue);
    }
	public function getPlaceholder(){
		return $this->_fieldPlaceholder;
	}
	public function setPlaceholder($placeholder){
		$this->_fieldPlaceholder = $placeholder;
	}
    public function setReplace($search, $replace) {
        $this->_macros[$search] = $replace;
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
        $this->_fieldNewValue = Input::sanitize($val);
    }

    # methods related to validation
    public function setValidator($v) {
        $this->_validateObject = $v;
    }
    public function hasValidation() {
        return (boolean)$this->_validateObject;
    }
    public function getValidator() {
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
        return $this->HTMLScript;
    }
    public function deleteMe() {
        return $this->getDeleteMe();
    }
    public function getDeleteMe() {
        return $this->_deleteMe;
    }
    public function setDeleteMe($val) {
        $this->_deleteMe = $val;
    }
    // if an inheriting class needs to adjust the snippets
    // they can do it by setting any of ...
    // ... setting HTMLPre, HTMLInput, HTMLPost directly
    // ... or by overriding this function to do something else
    public function fixSnippets() {
    }
}
