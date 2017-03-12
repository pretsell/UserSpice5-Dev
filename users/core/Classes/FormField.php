<?php
/*
 * Class inheritance related to Form Fields:
 * The rule with UserSpice is "Do NOT modify files under core/ - make the
 * change under local/ instead." For these Form Fields the way to do that is to
 * use the classes defined in local/Classes/FormFieldTypes.php and to make
 * modifications only to that file and local/Classes/FormField.php.
 *
 * core/Classes/FormField.php (this) defines class "US_FormField" which is
 * just the parent class. This class is abstract. Do not modify this file.
 *
 * local/Classes/FormField.php in turn defines class "FormField" which inherits
 * from class "US_FormField". Feel free to make changes to
 * local/Classes/FormField.php.
 *
 * core/Classes/FormFieldTypes.php in turn defines several classes such as
 * "US_FormField_Text", "US_FormField_Button", "US_FormField_Hidden", etc. which
 * inherit from class "FormField". Do not modify this file.
 *
 * local/Classes/FormFieldTypes.php in turn defines several classes such as
 * "FormField_Text", "FormField_Button", "FormField_Hidden", etc. which inherit
 * from the classes mentioned above which are named the same but with a "US_"
 * prefix. THESE ARE THE CLASSES YOU SHOULD USE IN YOUR CODE TO DEFINE FORM
 * FIELDS! Feel free to modify local/Classes/FormFieldTypes.php.
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
        $_isDBField=true, // whether this is from the primary table in the DB
        # for repeating elements, this class can control the SELECT
        # rather than setting it manually by setRepData(). This allows
        # things like pagination,
        $_sqlCols='',  // SELECT <this part here>
        $_sqlFrom='',  //   FROM <this part here>
        $_sqlWhere='', //  WHERE <this part here>
        $_sqlGroup='', //  <any GROUP BY or HAVING or etc>
        $_sqlOrder='', //  ORDER BY <this part here>
        $_sqlBindVals=[],//bind values matching ? or :var in sqlWhere
        $_pageItems=0, // paginates if non-zero and isRepeating() (i.e., this is the flag to turn on paginating)
        $_curPage=0,   // current page
        $_pageVarName='page',
        $_totalPages=0;// total number of pages
    public $repEmptyAlternateReplacesAll = true;
    public
        $HTML_Pre = '
            <div class="{DIV_CLASS}"> <!-- type={TYPE} id={FIELD_ID} name={FIELD_NAME} -->
              <label class="{LABEL_CLASS}" for="{FIELD_ID}">{LABEL_TEXT}
              <span class="{HINT_CLASS}" title="{HINT_TEXT}"></span></label>
              <br />',
        $HTML_Input = '
              <input class="{INPUT_CLASS}" type="{TYPE}" id="{FIELD_ID}" '
            .'name="{FIELD_NAME}" placeholder="{PLACEHOLDER}" value="{VALUE}" '
            .'{REQUIRED_ATTRIB} {EXTRA_ATTRIB} {DISABLED}>',
        $HTML_Post = '
            </div> <!-- {DIV_CLASS} (type={TYPE} id={FIELD_ID}, name={FIELD_NAME}) -->',
        $HTML_Script = '',
        $HTML_Page_Index = '<a href="?{PAGE_VAR_NAME}={PAGE_NUM}">{PAGE_NUM}</a>&nbsp;',
        $HTML_CurPage_Index = '<strong>{PAGE_NUM}</strong>&nbsp;',
        $elementList = ['Pre', 'Input', 'Post'];
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
        $MACRO_Value = '',
        $MACRO_Disabled = '',
        $MACRO_Page_Index = '';

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
            case 'valid':
            case 'validate':
            case 'validate_object':
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
            case 'sql_cols':
            case 'sqlcols':
                $this->setSQLCols($val);
                return true;
                break;
            case 'sql_from':
            case 'sqlfrom':
                $this->setSQLFrom($val);
                return true;
                break;
            case 'sql_where':
            case 'sqlwhere':
                $this->setSQLWhere($val);
                return true;
                break;
            case 'sql_group':
            case 'sqlgroup':
                $this->setSQLGroup($val);
                return true;
                break;
            case 'sql_order':
            case 'sqlorder':
                $this->setSQLOrder($val);
                return true;
                break;
            case 'sql_bindvals':
            case 'sqlbindvals':
                $this->setSQLBindVals($val);
                return true;
                break;
            case 'page_items':
            case 'pageitems':
                $this->setPageItems($val);
                return true;
                break;
            case 'page_var_name':
            case 'pagevar_name':
            case 'page_varname':
            case 'pagevarname':
                $this->setPageVarName($val);
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
        if (!$this->MACRO_Hint_Text && $this->hasValidation()) {
            $this->MACRO_Hint_Text = $this->getValidator()->describe($this->_fieldName);
        }
        $this->MACRO_Hint_Class = $this->getHintClass();
        return parent::getMacros($s, $opts);
    }

    public function calcRepData($insist=false) {
        $this->debug(1, '::calcRepData(): Entering');
        if (!$this->isRepeating() || ($this->getRepData() && !$insist)) {
            return false;
        }
        $this->debug(2, '::calcRepData(): Continuing');
        $cols = $this->getSQLCols();
        $from = $this->getSQLFrom();
        $where = $this->getSQLWhere();
        $groupBy = $this->getSQLGroup();
        $order = $this->getSQLOrder();
        $bindVals = $this->getSQLBindVals();
        $pageItems = $this->getPageItems();
        // these are required elements - use DUAL in the (unlikely) case
        // where you don't have a FROM table; use 1=1 in the case where
        // you don't have a WHERE clause
        if (!$cols || !$from || !$where) {
            return false;
        }
        $this->debug(2, '::calcRepData(): Still Continuing');
        if ($pageItems) {
            if ($this->getCurPage() < 1) {
                #dbg('varname='.$this->getPageVarName());
                if (!$cur = Input::get($this->getPageVarName())) {
                    $cur = 1;
                }
                $this->setCurPage($cur);
            }
            // paginating
            // calculate total number of pages
            $sql = "SELECT COUNT(*) AS c FROM $from $groupBy WHERE $where";
            $result = $this->_db->query($sql, $bindVals)->first();
            $totalPages = ceil($result->c / $pageItems);
            $this->setTotalPages($totalPages);
            $curPage = $this->getCurPage();
            if ($curPage > $totalPages) {
                $curPage = $totalPages;
            }
            $offset = max(0, ($curPage - 1)) * $pageItems;
            $this->setPageIndex();
        }
        $sql = "SELECT $cols FROM $from $groupBy WHERE $where";
        if ($order) {
            $sql .= " ORDER BY $order";
        }
        if ($pageItems) { // paginating
            $sql .= " LIMIT $offset,$pageItems";
        }
        $this->debug(3, "::calcRepData(): sql=$sql");
        $this->setRepData($this->_db->query($sql, $bindVals)->results());
        return (boolean)$this->getRepData();
    }
    public function setRepData($val) {
        $this->debug(1, '::setRepData(y): Entering');
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
        $this->debug(0, '::setRepData(): '.print_r($this->repData,true));
        return (boolean)$this->repData;
    }
    public function describeValidation() {
        return $this->getValidator()->describe($this->_fieldName);
    }
    public function getHintClass() {
        if ($this->getRequired()) {
            return $this->MACRO_Hint_Class_Required;
        } elseif ($this->MACRO_Hint_Text) {
            return $this->MACRO_Hint_Class_Not_Required;
        } else {
            return '';
        }
    }

    // For easy coding forms the key to the __construct hash on the field
    // list (1st arg) can provide both the field name and the display labels
    public function setDefaults($k) {
        if (!$this->getFieldName()) {
            $this->setFieldName($k);
        }
        if (!$this->getDBFieldName()) {
            $this->setDBFieldName($k);
        }
        $prettyText = ucwords(str_replace('_', ' ', $k));
        if (!$this->getFieldLabel()) {
            $this->setFieldLabel($prettyText);
        }
        if (!$this->getMacro('Label_Text')) {
            $this->setMacro('Label_Text', $prettyText);
        }
    }
    public function isChanged() {
        return ($this->_fieldNewValue != $this->_fieldValue);
    }
	public function getSQLCols(){
		return $this->_sqlCols;
	}
	public function setSQLCols($val){
		$this->_sqlCols = $val;
	}
	public function getSQLFrom(){
		return $this->_sqlFrom;
	}
	public function setSQLFrom($val){
		$this->_sqlFrom = $val;
	}
	public function getSQLWhere(){
		return $this->_sqlWhere;
	}
	public function setSQLWhere($val){
		$this->_sqlWhere = $val;
	}
	public function getSQLGroup(){
		return $this->_sqlGroup;
	}
	public function setSQLGroup($val){
		$this->_sqlGroup = $val;
	}
	public function getSQLOrder(){
		return $this->_sqlOrder;
	}
	public function setSQLOrder($val){
		$this->_sqlOrder = $val;
	}
	public function getSQLBindVals(){
		return $this->_sqlBindVals;
	}
	public function setSQLBindVals($val){
		$this->_sqlBindVals = $val;
	}
	public function getPageItems(){
		return $this->_pageItems;
	}
	public function setPageItems($val){
		$this->_pageItems = $val;
	}
	public function getCurPage(){
		return $this->_curPage;
	}
	public function setCurPage($val){
		$this->_curPage = $val;
	}
	public function getPageVarName(){
		return $this->_pageVarName;
	}
	public function setPageVarName($val){
		$this->_pageVarName = $val;
	}
	public function getTotalPages(){
		return $this->_totalPages;
	}
	public function setTotalPages($val){
		$this->_totalPages = $val;
	}
	public function setPageIndex(){
		$this->MACRO_Page_Index = '';
        #dbg("curpage=".$this->getCurPage().', totalpages='.$this->getTotalPages());
        if ($this->getTotalPages() > 1) { // don't bother printing links if just 1
            $macros = ['{PAGE_VAR_NAME}'=>$this->getPageVarName(), '{LAST_PAGE_NUM}'=>$this->getTotalPages()];
            for ($i = max(1, $this->getCurPage()-5); $i <= min($this->getTotalPages(), $this->getCurPage()+5); $i++) {
                #dbg("Processing $i");
                $macros['{PAGE_NUM}'] = $i;
                if ($i == $this->getCurPage()) {
                    $this->MACRO_Page_Index .= str_replace(array_keys($macros), array_values($macros), $this->HTML_CurPage_Index);
                } else {
                    $this->MACRO_Page_Index .= str_replace(array_keys($macros), array_values($macros), $this->HTML_Page_Index);
                }
            }
        }
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
    // if construct used array rather than object then convert to
    // object. Intentionally late conversion because we need the
    // (possibly late defaulted) values for display and field name
    public function fixValidator() {
        $v = $this->_validateObject;
        if (!isset($v['display']) && ($d = $this->getFieldLabel())) {
            $v['display'] = $d;
        }
        $args = [$this->getDBFieldName() => $v];
        $this->_validateObject = new Validate($args);
    }
    public function hasValidation() {
        return (boolean)$this->_validateObject;
    }
    public function getValidator($createIfNeeded=true) {
        if ($createIfNeeded && !$this->_validateObject) {
            $this->setValidator(new Validate());
        } else {
            if (is_array($this->_validateObject)) {
                $this->fixValidator(); // fix (late) if needed
            }
        }
        return $this->_validateObject;
    }

    public function setDisabled($val) {
        if ($val) {
            $this->MACRO_Disabled = 'disabled';
        } else {
            $this->MACRO_Disabled = '';
        }
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

    public function getHTML($opts=[]) {
        $this->calcRepData();
        return parent::getHTML($opts);
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