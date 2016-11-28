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
        $_isDBField=true, // whether this is a field in the DB
        $_hintClass_notRequired = 'fa fa-info-circle',
        $_hintClass_required = 'fa fa-asterisk',
        $_tableClass = "table table-hover",
        $_tableHeadCellClass = "",
        $_tableDataCellClass = "",
        $_tableHeadRowClass = "",
        $_tableDataRowClass = "",
        $_tableDataColClass = "",
        $_dataFields = [],
        $_dataFieldLabels = [],
        $_repeatAliases = ['{OPTION-VALUE}', '{OPTION-LABEL}'], // this will provide an alias
                            // replacement text for the first n values in each element of $_repeatValues
                            // thus '{OPTION-LABEL}' will be replaced with 'label1' and then 'label2'
                            // using the example data below
        $_repeatValues = []; // e.g., [['id'=>1, 'name'=>'label1'], ['id'=>2, 'name'=>'label2'], ...]
                            // this will provide replace macros for {ID} and {NAME}, respectively
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
             </div>',
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
                case 'is_dbfield':
                case 'is_db_field':
                case 'isdbfield':
                    $this->setIsDBField($v);
                    break;
                case 'repeat':
                case 'repeatvalue':
                    $this->setRepeatValues($v);
                    break;
                case 'fields':
                case 'datafields':
                    $this->setDataFields($v);
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
                        $this->setReplace('{'.strtoupper($k).'}', $v);
                    }
                    #dbg("__construct: _macros=<pre>".print_r($this->_macros,true)."</pre><br />");
                    // else ... don't do anything - may have come from extra DB columns
            }
        }
        if (is_null($this->getPlaceholder())) {
            $this->setPlaceholder($this->getFieldLabel());
        }
    }

    # $opts is a hash which can have the following indexed values:
    #  'replaces' => ['{search}'=>'replace',...]
    public function getHTML($opts=[]) {
        # Start by calculating a $this->HTMLInput. If this is a repeating
        # field (select with options, for instance) then each row in
        # $this->_repeatValues gets its own copy of $this->HTMLInput with
        # the values appropriately substituted
        $html = '';
        $inter_br = '';
        if ($this->_repeatValues) {
            foreach ($this->_repeatValues as $repeatVal) {
                $tmprepl = [];
                $tmprepl['{INTER_BR}'] = $inter_br; // {INTER_BR} must be at *start* of element
                $aliasIdx = 0;
                $dataRow = '';
                foreach ($this->getDataFields() as $fld) {
                    if (isset($repeatVal[$fld])) {
                        $val = $repeatVal[$fld];
                    } else {
                        $val = $fld;
                    }
                    $dataRow .= '<td>'.$val.'</td>';
                }
                $tmprepl['{TABLE-DATA-CELLS}'] = $dataRow;
                foreach ($repeatVal as $fld=>$val) {
                    $tmprepl['{'.strtoupper($fld).'}'] = $val;
                    if (isset($this->_repeatAliases[$aliasIdx])) {
                        $tmprepl[$this->_repeatAliases[$aliasIdx]] = $val;
                    }
                    $aliasIdx++;
                }
                $html .= str_replace(array_keys($tmprepl), array_values($tmprepl), $this->HTMLInput)."\n";
                $inter_br = "<br />\n";
            }
        } else {
            $html = $this->HTMLInput;
        }
        # Now $html holds $this->HTMLInput with data replaced. Now we will calculate
        # an array of macros for search/replace. Static values are already in
        # $this->_macros but others have to be set "just in time"...
        $justInTimeRepl = [
                    '{TYPE}'           => $this->getFieldType(),
                    '{FIELD-NAME}'     => $this->getFieldName(),
                    '{FIELD-ID}'       => $this->getFieldId(),
                    '{LABEL-TEXT}'     => $this->getFieldLabel(),
                    '{PLACEHOLDER}'    => $this->getPlaceholder(),
                    '{VALUE}'          => $this->getFieldValue(),
                    '{REQUIRED-ATTRIB}'=> ($this->getRequired() ? 'required' : ''),
                    '{HINT-CLASS}'     => $this->getHintClass(),
                    '{TABLE-CLASS}'    => $this->getTableClass(),
                    '{TABLE-HEAD-CELLS}'=> $this->getTableHeadCells(),
                    '{TD-ROW-CLASS}'   => $this->getTableDataRowClass(),
                    '{TH-ROW-CLASS}'   => $this->getTableHeadRowClass(),
                    '{TD-CLASS}'       => $this->getTableDataCellClass(),
                    '{TH-CLASS}'       => $this->getTableHeadCellClass(),
        ];
        $repl = array_merge($this->_macros, $justInTimeRepl, (array)@$opts['replaces']);
        # since this is slightly "expensive" we won't evaluate unless it is needed
        if (!isset($repl['{HINT-TEXT}']) && $this->getValidator()) {
            $repl['{HINT-TEXT}'] = $this->getValidator()->describe($this->_fieldName);
        }
        $html = str_replace(array_keys($repl), array_values($repl), $this->HTMLPre . $html . $this->HTMLPost);
        return $html;
    }

    public function describeValidation() {
        return $this->getValidator()->describe($this->_fieldName);
    }
    public function getTableHeadCellClass() {
        return $this->_tableHeadCellClass;
    }
    public function getTableDataCellClass() {
        return $this->_tableDataCellClass;
    }
    public function getTableHeadRowClass() {
        return $this->_tableHeadRowClass;
    }
    public function getTableDataRowClass() {
        return $this->_tableDataRowClass;
    }
    public function getTableClass() {
        return $this->_tableClass;
    }
    public function getDataFields() {
        return $this->_dataFields;
    }
    public function setDataFields($val) {
        $this->_dataFieldLabels = array_values($val);
        $this->_dataFields = array_keys($val);
    }
    public function getTableHeadCells() {
        $html = '';
        foreach ($this->_dataFieldLabels as $l) {
            $html .= '<th>'.$l.'</th>';
        }
        return $html;
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
    public function setRepeatValues($opts=array()) {
        if (sizeof($opts)>0 && is_object($opts[0])) {
            # convert from object ($data->id, $data->name) to associative array
            # ($data['id'], $data['name']) if needed
            $tmp = [];
            foreach ((array)$opts as $o) {
                $tmp[] = (array)$o;
            }
            $this->_repeatValues = $tmp;
        } else {
            $this->_repeatValues = $opts;
        }
    }
    public function addRepeatValue($opts=array()) {
        $this->_repeatValues[] = $opts;
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
    // if an inheriting class needs to adjust the snippets
    // they can do it by setting any of ...
    // ... setting HTMLPre, HTMLInput, HTMLPost directly
    // ... or by overriding this function to do something else
    public function fixSnippets() {
    }
}
