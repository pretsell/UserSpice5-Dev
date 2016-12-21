<?php
/*

*/
class US_Form extends Element {
	protected $_formName,
        $_fields=[],
        $_validateObject=null,
        $_validatePassed=false,
        $_dbTable=null;
    # These flags determine whether the corresponding blocks
    # are used for this form. They do not have get...() or
    # set...() functions and can be set directly.
    public $elementList = [
        'Header', 'openContainer', 'AdminDashboard',
        'TitleAndResults', 'openForm', 'CSRF', 'openRow', 'openCol',
        'Fields',
        'closeCol', 'closeRow', 'closeForm',
        'PageFooter', 'Footer',
    ];
    public $repElement = 'Fields';
    protected $HTML_openContainer = '<div class="container {CONTAINER_CLASS}">'."\n";
    protected $HTML_openRow = '<div class="row {ROW_CLASS}">'."\n";
    protected $HTML_openCol = '<div class="{COL_CLASS}">'."\n";
    protected $HTML_openForm = '<form name="{FORM_NAME}" action="{FORM_ACTION}" method="{FORM_METHOD}">'."\n";
    protected $HTML_CSRF = '<input type="hidden" name="csrf" value="{GENERATE_CSRF_TOKEN}">'."\n";
    protected $HTML_closeForm = '</form>'."\n";
    protected $HTML_closeContainer = '</div> <!-- container -->'."\n";
    protected $HTML_closeRow = '</div> <!-- row -->'."\n";
    protected $HTML_closeCol = '</div> <!-- col -->'."\n";
    protected $HTML_openWell = '<div class="well {WELL_CLASS}">'."\n";
    protected $HTML_closeWell = '</div> <!-- well -->'."\n";
    protected $HTML_Title = '
        <div class="{COL_CLASS}">
            <h2>{FORM_TITLE}</h2>
        </div> <!-- title col -->
        ';
    protected $HTML_Well_Title = '
        <div class="{COL_CLASS}">
            <h2>{FORM_TITLE}</h2>
        </div> <!-- title col -->
        ';
    protected $HTML_TitleAndResults = '
        <div class="{COL_CLASS}">
            <h2>{FORM_TITLE}</h2>
    		{RESULT_BLOCK}
        </div> <!-- title & result col -->
        ';
    protected $HTML_AdminDashboard = '
        <div class="xs-col-12">
            {INCLUDE_ADMIN_DASHBOARD}
        </div> <!-- admin dashboard col -->
        ';
    # others will be added - these are just the static, known ones
    public $MACRO_Container_Class = '',
        $MACRO_Tab_Class = '',
        $MACRO_Tab_Content_Class = '',
        $MACRO_Tab_Pane_Class = 'xs-col-12',
        $MACRO_Row_Class = '',
        $MACRO_Col_Class = 'xs-col-12',
        $MACRO_Form_Name = 'usForm',
        $MACRO_Form_Method = 'post',
        $MACRO_Form_Action = '',
        $MACRO_Browser_Title = '',
        $MACRO_Form_Title = '',
        $MACRO_Tab_Pane_Active = '',
        $MACRO_Tab_Id = '';

	public function __construct($fields=[], $opts=[]){
        $opts = array_merge([$this->repElement=>$fields], $opts);
        parent::__construct($opts);
        // $formName is usually set prior to master_form.php
        if (!$this->_formName) {
            if ($GLOBALS['formName']) {
                $this->_formName = $GLOBALS['formName'];
            }
        }
        if (!$this->getMacro('Form_Title')) {
            $this->setTitleByPage();
        }
        // delete conditional fields (keep_if or delete_if logic in $opts)
        foreach ($this->getFields() as $fieldName=>$fieldObj) {
            if ($fieldObj->deleteMe()) {
                $this->deleteField($fieldName);
            }
        }
	}
    public function handle1Opt($name, $val) {
        switch (strtolower($name)) {
            case 'titletoken':
            case 'title_lang':
                $val = lang($val);
                # no break - falling through with new $val
            case 'title':
                # NOTE: may fall through from above
                $this->setMacro('Form_Title', $val);
                return true;
                break;
            case 'table':
            case 'dbtable':
                $this->setDBTable($val);
                return true;
                break;
            case 'active_tab':
                $this->setTabIsActive($val);
                return true;
                break;
            case 'tab_id':
                $this->setTabId($val);
                return true;
                break;
        }
        if (parent::handle1Opt($name, $val)) {
            return true;
        }
        return false;
    }
    public function setTitleByPage() {
        $page = $this->_db->query("SELECT * FROM pages WHERE page = ?", [$this->_formName]);
        if ($page->count() > 0) {
            $pageRow = $page->first();
            $this->setMacro('Form_Title', lang($pageRow->title_lang));
        }
    }

    public function getHTMLHeader($opts=[], $noFill=false) {
        return getInclude(pathFinder('includes/header.php'));
    }
    public function getHTMLNavigation($opts=[], $noFill=false) {
        return getInclude(pathFinder('includes/navigation.php'));
    }
    public function getHTMLPageFooter($opts=[], $noFill=false) {
        return getInclude(pathFinder('includes/page_footer.php'));
    }
    public function getHTMLFooter($opts=[], $noFill=false) {
        $html = getInclude(pathFinder('includes/html_footer.php'));
        foreach ($this->getAllFields() as $field) {
            if (method_exists($field, 'getHTML_Scripts')) {
                $html .= $field->getHTMLScripts();
            }
        }
        return $html;
    }

    public function getMacros($s, $opts) {
        $macros = parent::getMacros($s, $opts);
        # These macros are "expensive" to evaluate and so are only
        # evaluated if they actually exist in the $s string
        if (strpos($s, "{RESULT_BLOCK}") !== false) {
            $macros['{RESULT_BLOCK}'] = ResultBlock((array)@$opts['errors'], (array)@$opts['successes']);
        }
        if (strpos($s, "{GENERATE_CSRF_TOKEN}") !== false) {
            $macros['{GENERATE_CSRF_TOKEN}'] = Token::generate();
        }
        if (strpos($s, "{INCLUDE_ADMIN_DASHBOARD}") !== false) {
            $macros['{INCLUDE_ADMIN_DASHBOARD}'] = getInclude(pathFinder('includes/admin_dashboard.php'));
        }
        return $macros;
    }

    public function setTabIsActive($val) {
        $this->MACRO_Tab_Pane_Active = $val;
    }
    public function setTabId($val) {
        $this->MACRO_Tab_Id = $val;
    }
    public function setDBTable($table) {
        $this->_dbTable = $table;
    }
    public function getDBTable() {
        return $this->_dbTable;
    }
	public function setAction($action) {
		$this->MACRO_Form_Action=$action;
	}
    public function getAllFields($fieldFilter=[], $opts=[]) {
        $opts = array_merge($opts, ['recursive'=>true]);
        return $this->getFields($fieldFilter, $opts);
    }
    public function getFields($fieldFilter=[], $opts=[]) {
        #dbg("getFields(): Entering");
        #dbg("opts=".print_r($opts,true));
        # default to $onlyFields==true unless override
        $onlyFields = !(@$opts['not_only_fields'] || in_array('not_only_fields', $opts));
        $recursive = (@$opts['recursive'] || in_array('recursive', $opts));
        $fieldList = $this->_fixFieldList($fieldFilter, $onlyFields);
        $rtn = [];
        foreach ($fieldList as $f) {
            $field = $this->getField($f);
            if (isset($opts['class']) && is_a($field, $opts['class'])) {
                $rtn[$f] = $field;
            } else {
                if ($recursive && method_exists($field, 'getFields')) {
                    // allow for forms nested in forms (FormTab_Contents and FormTab_Pane, etc.)
                    $rtn = array_merge($rtn, $field->getFields($fieldFilter, $opts));
                } elseif (!isset($opts['class'])) {
                    $rtn[$f] = $field;
                }
            }
        }
        #dbg("getFields(): Returning<pre>".print_r($rtn,true)."</pre><br />\n");
        return $rtn;
    }
    public function getField($fieldName) {
        if (isset($this->repData[$fieldName])) {
            # normal field
            return $this->repData[$fieldName];
        } else {
            # perhaps it's in a FormTab_Contents or other form-section class
            foreach ($this->repData as $k=>$v) {
                if (method_exists($v, 'getField') && ($f = $v->getField($fieldName))) {
                    return $f;
                }
            }
        }
    }
    public function deleteField($fieldName) {
        unset($this->repData[$fieldName]);
    }
    public function listFields($onlyFields=true) {
        #dbg("listFields($onlyFields): entering");
        $rtn = array_keys($this->repData);
        if ($onlyFields) {
            $rtn = array_filter($rtn, 'is_string'); // get rid of numeric keys (HTML snippets)
        }
        #dbg("listFields(): Returning <pre>".print_r($rtn,true)."</pre><br />");
        return $rtn;
    }
    public function setFieldValues($vals, $fieldFilter=array()) {
        $fieldList = $this->_fixFieldList($fieldFilter);
        foreach ($fieldList as $f) {
            if (is_array($vals)) {
                if (isset($vals[$f])) {
                    $this->getField($f)->setFieldValue($vals[$f]);
                }
            } else { # presumably it is an object
                $curObj = $this->getField($f);
                // handle nested forms (like for tabs)
                if (method_exists($curObj, 'setFieldValues')) {
                    $curObj->setFieldValues($vals, $fieldFilter);
                } elseif (isset($vals->$f)) {
                    #if (!method_exists($curObj, 'setFieldValue')) { dbg( 'class='.get_class($curObj).', parent='.get_parent_class($curObj)); var_dump($curObj); }
                    $curObj->setFieldValue($vals->$f);
                }
            }
        }
    }
    public function setNewValues($vals, $fieldFilter=array()) {
        $fieldList = $this->_fixFieldList($fieldFilter);
        foreach ($fieldList as $f) {
            if (isset($vals[$f])) {
                $this->getField($f)->setNewValue($vals[$f]);
            }
        }
    }
    public function fieldListNewValues($fieldFilter=array(), $onlyDB=true) {
        $fieldList = $this->_fixFieldList($fieldFilter);
        $rtn = [];
        #var_dump($fieldList);
        foreach ($fieldList as $f) {
            if (!$onlyDB || $this->getField($f)->getIsDBField()) {
                if (($newVal = $this->getField($f)->getNewValue()) !== null) {
                    $rtn[$f] = $newVal;
                }
            }
        }
        return $rtn;
    }
    # If someone wants a sub-set of fields, make sure they are all in the form
    protected function _fixFieldList($fieldFilter, $onlyFields=true) {
        if ($fieldFilter) {
            return array_intersect($this->listFields($onlyFields), $fieldFilter);
        } else {
            return $this->listFields($onlyFields);
        }
    }
    public function isChanged($fieldFilter=array()) {
        $fieldList = $this->_fixFieldList($fieldFilter);
        foreach ($fieldList as $f) {
            if ($this->getField($f)->isChanged()) {
                return true;
            }
        }
        return false;
    }

	public function getFormName() {
		return $this->_formName;
	}
	public function setFormName($name) {
		$this->_formName=$name;
	}

    public function updateIfChangedAndValid($id, &$errors, $fieldFilter=[]) {
        if (!$table = $this->getDBTable()) {
            $errors[] = 'ERROR: No table specified';
            return false;
        }
        if ($this->isChanged()) {
            $fields = $this->fieldListNewValues($fieldFilter, true);
            if ($this->checkFieldValidation($fields, $errors)) {
                if ($this->_db->update($table, $id, $fields)) {
                    return true;
                } else {
                    $errors[] = lang('SQL_ERROR');
                    return false;
                }
            }
        }
    }
    # Delete rows identified by $ids in the DB table for this form
    public function delete($ids, &$errors) {
        if (!$ids) {
            return true;
        }
        if (!$table = $this->getDBTable()) {
            $errors[] = 'ERROR: No table specified';
            return false;
        }
        $count = 0;
        foreach ($ids as $id) {
            $count += $db->delete($table, $id)->count();
        }
        return $count;
    }
	public function checkFieldValidation($data=[], &$errors, $fieldFilter=array()) {
        $fieldList = $this->_fixFieldList($fieldFilter);
        $this->_validatePassed = true; // assume it passes unless we get errors
        $errors = [];
        foreach ($fieldList as $f) {
            # some fields (a submit button, for instance, don't have validation)
            if (!$this->getField($f)->dataIsValid($data)) {
                $this->_validatePassed = false;
                $errors = $this->getField($f)->stackErrorMessages($errors);
            }
        }
        $this->_validateErrors = $errors;
        return $this->_validatePassed;
	}

    # must be called AFTER checkFieldValidation() - tells whether the last validation
    # was successfully passed or not
	public function passedFieldValidation() {
		return $this->_validatePassed;
	}

	public function getFormValidationErrors() {
		$validateErrors=$this->_validateObject->errors();
		$this->_validateObject=null;
		return $validateErrors;
	}

    public function addField($fieldName, $formFieldObj) {
        $this->repData[$fieldName] = $formFieldObj;
    }
    public function deleteMe() {
        return false; // forms don't delete themselves
    }
}
