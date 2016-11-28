<?php
/*

*/
class US_Form {
    private $_db = null;
	protected $_formName,
        $_formAction='',
        $_fields=[],
        $_validateObject=null,
        $_validatePassed=false,
        $_dbTable=null,
        $_browserTitle=null;
    # These flags determine whether the corresponding blocks
    # are used for this form. They do not have get...() or
    # set...() functions and can be set directly.
    public
        $useCSRF=true,
        $useHeader=true,
        $useNavigation=true,
        $useContainer=true,
        $useTab=false, // used for class "FormTab"
        $useAdminDashboard=true,
        $useTitle=false, // see TitleAndResult below
        $useTitleAndResults=true,
        $useForm=true,
        $useRowCol=true,
        $usePageFooter=true,
        $useHTMLFooter=true;
    protected $_formTitle='';
    protected $_tabIsActive='';
    protected $_tabId='';
    protected $_openContainerBlock = '<div class="container {CONTAINER-CLASS}">'."\n";
    protected $_openRowBlock = '<div class="row {ROW-CLASS}">'."\n";
    protected $_openColBlock = '<div class="{COL-CLASS}">'."\n";
    protected $_openForm = '<form name="{FORM-NAME}" action="{FORM-ACTION}" method="{FORM-METHOD}">'."\n";
    protected $_csrfToken = '<input type="hidden" name="csrf" value="{GENERATE-CSRF-TOKEN}">'."\n";
    protected $_closeForm = '</form>'."\n";
    protected $_closeContainerBlock = '</div> <!-- container -->'."\n";
    protected $_closeRowBlock = '</div> <!-- row -->'."\n";
    protected $_closeColBlock = '</div> <!-- col -->'."\n";
    protected $_openTabBlock = '
        <div class="tab-pane {TAB-PANE-ACTIVE} {TAB-PANE-CLASS}" id="{TAB-ID}">
        ';
    protected $_closeTabBlock = '
        </div> <!-- tab-pane -->
        ';
    protected $_titleBlock = '
        <div class="{COL-CLASS}">
            <h2>{FORM-TITLE}</h2>
        </div> <!-- title col -->
        ';
    protected $_titleAndResultBlock = '
        <div class="{COL-CLASS}">
            <h2>{FORM-TITLE}</h2>
    		{RESULT-BLOCK}
        </div> <!-- title & result col -->
        ';
    protected $_adminDashboardBlock = '
        <div class="xs-col-12">
            {INCLUDE-ADMIN-DASHBOARD}
        </div> <!-- admin dashboard col -->
        ';
    # others will be added - these are just the static, known ones
    protected $_macros = [
        '{CONTAINER-CLASS}' => '',
        '{TAB-CLASS}'       => '',
        '{TAB-CONTENT-CLASS}'=> '',
        '{TAB-PANE-CLASS}'  => 'xs-col-12',
        '{ROW-CLASS}'       => '',
        '{COL-CLASS}'       => 'xs-col-12',
        '{FORM-NAME}'       => 'usForm',
        '{FORM-METHOD}'     => 'post',
        '{FORM-ACTION}'     => '',
    ];

	public function __construct($fields=[], $opts=[]){
        $this->_db = DB::getInstance();
		$this->setAllFields($fields, $opts);
        $this->setOpts($opts);
        if (!$this->_formName) {
            if ($GLOBALS['formName']) {
                $this->_formName = $GLOBALS['formName'];
            }
        }
        if (!$this->getTitle()) {
            $this->setTitleByPage();
        }
	}
    public function setOpts($opts) {
        foreach ($opts as $k=>$v) {
            switch ($k) {
                case 'titleToken':
                    $v = lang($v);
                    # no break - falling through with new $v
                case 'title':
                    # NOTE: may fall through from above
                    $this->setTitle($v);
                    break;
                case 'table':
                case 'dbtable':
                    $this->setDBTable($v);
                    break;
                case 'conditional_fields':
                    foreach ($v as $fn=>$cond) {
                        if (!$cond) {
                            $this->deleteField($fn);
                        }
                    }
                    break;
                case 'active_tab':
                    $this->setTabIsActive($v);
                    break;
                case 'tab_id':
                    $this->setTabId($v);
                    break;
                # default - do nothing - this $opt is for elsewhere
            }
        }
    }
    public function setTitleByPage() {
        $page = $this->_db->query("SELECT * FROM pages WHERE page = ?", [$this->_formName]);
        if ($page->count() > 0) {
            $pageRow = $page->first();
            $this->setTitle(lang($pageRow->title_lang));
        }
    }

    public function getHTML($opts=[]) {
        $html = '';
        $html .= $this->getHTMLStart($opts, true);
        $html .= $this->getHTMLOpenRowCol($opts, true);
        $html .= $this->getHTMLOpenForm($opts, true);
        $html .= $this->getHTMLOpenTab($opts, true);
        $html .= $this->getHTMLFields([], false, $opts, true);
        $html .= $this->getHTMLCloseTab($opts, true);
        $html .= $this->getHTMLCloseForm($opts, true);
        $html .= $this->getHTMLCloseColRow($opts, true);
        $html .= $this->getHTMLCloseContainer($opts, true);
        $html .= $this->getHTMLPageFooter($opts, true);
        $html .= $this->getHTMLFooter($opts, true);
        return $this->fillHTML($html, $opts);
    }

    public function getHTMLFields($fieldFilter=[], $onlyFields=false, $opts=[], $noFill=false) {
        $fieldList = $this->_fixFieldList($fieldFilter, $onlyFields);
        $html = "\n";
        foreach ($fieldList as $f) {
            if (is_object($fld = $this->getField($f))) {
                $html .= $fld->getHTML()."\n";
            } else {
                $html .= $fld; // just additional HTML
            }
        }
        $html .= "\n";
        return $this->fillHTML($html, $opts, $noFill);
    }

    public function wantBlock($flag, $optIdx, $opts) {
        if ($flag) {
            return (!isset($opts['no'.$optIdx]) && !in_array('no'.$optIdx, $opts));
        } else {
            return (isset($opts[$optIdx]) || in_array($optIdx, $opts));
        }
    }
    public function getHTMLHeader($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useHeader, 'header', $opts)) {
            return $this->fillHTML(getInclude(pathFinder('includes/header.php')), $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLOpenContainer($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useContainer, 'container', $opts)) {
            return $this->fillHTML($this->_openContainerBlock, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLCloseContainer($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useContainer, 'container', $opts)) {
            return $this->fillHTML($this->_closeContainerBlock, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLOpenTab($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useTab, 'tab', $opts)) {
            return $this->fillHTML($this->_openTabBlock, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLCloseTab($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useTab, 'tab', $opts)) {
            return $this->fillHTML($this->_closeTabBlock, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLTitleAndResults($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useTitleAndResults, 'title_and_result', $opts)) {
            return $this->fillHTML($this->_titleAndResultBlock, $opts, $noFill);
        } else {
            return '';
        }
    }
    # You probably want TitleAndResults above... Typically this (title by itself)
    # is disabled by $this->useTitle
    public function getHTMLTitle($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useTitle, 'title', $opts)) {
            return $this->fillHTML($this->_titleBlock, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLAdminDashboard($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useAdminDashboard, 'admin_dashboard', $opts)) {
            return $this->fillHTML($this->_adminDashboardBlock, $opts, $noFill);
        } else {
            return '';
        }
    }

    public function getHTMLStart($opts=[], $noFill=false) {
        $html = $this->getHTMLHeader($opts, true);
        $html .= $this->getHTMLOpenContainer($opts, true);
        $i = $this->wantBlock($this->useTitle, 'title', $opts) ||
            $this->wantBlock($this->useTitle, 'title_and_result', $opts) ||
            $this->wantBlock($this->useAdminDashboard, 'admin_dashboard', $opts);
        if ($i) {
            $html .= $this->getHTMLOpenRowCol($opts, true);
        }
        # By default this (Title by itself) is not included
        $html .= $this->getHTMLTitle($opts, true);
        # This (TitleAndResults) is preferred
        $html .= $this->getHTMLTitleAndResults($opts, true);
        $html .= $this->getHTMLAdminDashboard($opts, true);
        if ($i) {
            $html .= $this->getHTMLCloseColRow($opts, true);
        }
        return $this->fillHTML($html, $opts, $noFill);
    }
    public function getHTMLOpenRowCol($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useRowCol, 'rowcol', $opts)) {
            $html = $this->_openRowBlock . $this->_openColBlock;
            return $this->fillHTML($html, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLCloseColRow($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useRowCol, 'rowcol', $opts)) {
            $html = $this->_closeColBlock . $this->_closeRowBlock;
            return $this->fillHTML($html, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLOpenForm($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useForm, 'form', $opts)) {
            $html = $this->_openForm;
            if ($this->wantBlock($this->useCSRF, 'csrf', $opts)) {
                $html .= $this->_csrfToken;
            }
            return $this->fillHTML($html, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLCloseForm($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useForm, 'form', $opts)) {
            return $this->fillHTML($this->_closeForm, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLNavigation($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useNavigation, 'navigation', $opts)) {
            $html = getInclude(pathFinder('includes/navigation.php'));
            return $this->fillHTML($html, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLPageFooter($opts=[], $noFill=false) {
        if ($this->wantBlock($this->usePageFooter, 'page_footer', $opts)) {
            $html = getInclude(pathFinder('includes/page_footer.php'));
            return $this->fillHTML($html, $opts, $noFill);
        } else {
            return '';
        }
    }
    public function getHTMLFooter($opts=[], $noFill=false) {
        if ($this->wantBlock($this->useHTMLFooter, 'html_footer', $opts)) {
            $html = getInclude(pathFinder('includes/html_footer.php'));
            foreach ($this->getAllFields() as $field) {
                if (method_exists($field, 'getHTMLScripts')) {
                    $html .= $field->getHTMLScripts();
                }
            }
            return $this->fillHTML($html, $opts, $noFill);
        } else {
            return '';
        }
    }

    # This "Form" class is based on replacing {MACROS} in the various
    # blocks. This method (fillHTML()) does the actual find/replace
    # of those {MACROS}.
    public function fillHTML($s, $opts, $noFill=false) {
        if ($noFill) {
            return $s;
        }
        #dbg("fillHTML: $s<br />\n");
        $optMacros = (isset($opts['replaces']) ? $opts['replaces'] : (isset($opts['macros']) ? $opts['macros'] : []));
        $repl = array_merge($this->_macros,
                    [
                        '{BROWSER-TITLE}'=>$this->_browserTitle,
                        '{FORM-TITLE}'=>$this->getTitle(),
                        '{TAB-PANE-ACTIVE}'=>$this->getTabIsActive(),
                        '{TAB-ID}'=>$this->getTabId(),
                    ],
                    $optMacros);
        # These macros are "expensive" to evaluate and so are only
        # evaluated if they actually exist in the $s string
        if (strpos($s, "{RESULT-BLOCK}") !== false) {
            $repl['{RESULT-BLOCK}'] = ResultBlock((array)@$opts['errors'], (array)@$opts['successes']);
        }
        if (strpos($s, "{GENERATE-CSRF-TOKEN}") !== false) {
            $repl['{GENERATE-CSRF-TOKEN}'] = Token::generate();
        }
        if (strpos($s, "{INCLUDE-ADMIN-DASHBOARD}") !== false) {
            $repl['{INCLUDE-ADMIN-DASHBOARD}'] = getInclude(pathFinder('includes/admin_dashboard.php'));
        }
        return str_replace(array_keys($repl), array_values($repl), $s);
    }

    public function setTitle($title) {
        $this->_formTitle = $title;
    }
    public function getTitle() {
        return $this->_formTitle;
    }
    public function setTabIsActive($val) {
        $this->_tabIsActive = $val;
    }
    public function getTabIsActive() {
        return $this->_tabIsActive;
    }
    public function setTabId($val) {
        $this->_tabId = $val;
    }
    public function getTabId() {
        return $this->_tabId;
    }
    public function setDBTable($table) {
        $this->_dbTable = $table;
    }
    public function getDBTable() {
        return $this->_dbTable;
    }
	public function setAction($action) {
		$this->_formAction=$action;
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
        if (isset($this->_fields[$fieldName])) {
            # normal field
            return $this->_fields[$fieldName];
        } else {
            # perhaps it's in a FormTab_Contents or other form-section class
            foreach ($this->_fields as $k=>$v) {
                if (method_exists($v, 'getField') && ($f = $v->getField($fieldName))) {
                    return $f;
                }
            }
        }
    }
    public function deleteField($fieldName) {
        unset($this->_fields[$fieldName]);
    }
    public function listFields($onlyFields=true) {
        #dbg("listFields($onlyFields): entering");
        $rtn = array_keys($this->_fields);
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
                if (isset($vals->$f)) {
                    $this->getField($f)->setFieldValue($vals->$f);
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
    private function _fixFieldList($fieldFilter, $onlyFields=true) {
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

	public function passedFieldValidation() {
		return $this->_validatePassed;
	}

	public function getFormValidationErrors() {
		$validateErrors=$this->_validateObject->errors();
		$this->_validateObject=null;
		return $validateErrors;
	}

    public function addField($fieldName, $formFieldObj) {
        $this->_fields[$fieldName] = $formFieldObj;
    }
	public function setAllFields($fields, $opts) {
		$this->_fields=$fields;
	}
}
