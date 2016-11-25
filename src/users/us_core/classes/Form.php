<?php
/*

*/
class US_Form {
	protected $_formName,
        $_formAction='',
        $_fields=[],
        $_validateObject=null,
        $_validatePassed=false,
        $_dbTable=null,
        $_browserTitle=null;
    protected $_formTitle='TITLE NOT SET';
    protected $_openContainerBlock = '<div class="container {CONTAINER-CLASS}">';
    protected $_openRowBlock = '<div class="row {ROW-CLASS}">';
    protected $_openColBlock = '<div class="{COL-CLASS}">';
    protected $_openForm = '<form name="{FORM-NAME}" action="{FORM-ACTION}" method="{FORM-METHOD}">';
    protected $_csrfToken = '<input type="hidden" name="csrf" value="{GENERATE-CSRF-TOKEN}">';
    protected $_closeForm = '</form>';
    protected $_closeContainerBlock = '</div>';
    protected $_closeRowBlock = '</div>';
    protected $_closeColBlock = '</div>';
    protected $_titleBlock = '
        <div class="{COL-CLASS}">
            <h2>{FORM-TITLE}</h2>
    		{RESULT-BLOCK}
        </div> <!-- col -->';
    protected $_adminDashboardBlock = '
        <div class="xs-col-12">
            {INCLUDE-ADMIN-DASHBOARD}
        </div> <!-- col -->';
    # others will be added - these are just the static, known ones
    protected $_replaces = [
        '{CONTAINER-CLASS}' => '',
        '{ROW-CLASS}'       => '',
        '{COL-CLASS}'       => 'xs-col-12',
        '{FORM-NAME}'       => 'usForm',
        '{FORM-METHOD}'     => 'post',
        '{FORM-ACTION}'     => '',
    ];

	public function __construct($fields=[], $opts=[]){
		$this->setAllFields($fields, $opts);
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
                # default - do nothing - this $opt is for elsewhere
            }
        }
        if (!$this->_formName) {
            if ($GLOBALS['formName']) {
                $this->_formName = $GLOBALS['formName'];
            }
        }
        $db = DB::getInstance();
        $page = $db->query("SELECT * FROM pages WHERE page = ?", [$this->_formName]);
        if ($page->count() > 0) {
            $pageRow = $page->first();
            $this->setTitle(lang($pageRow->title_lang));
        }
	}

    public function getHTML($opts=[]) {
        $html = '';
        $html .= getHTMLStart($opts);
        $html .= $this->_openRowBlock . $this->_openColBlock . $this->_openForm;
        # include csrf token field unless specified nocsrf in $opts
        if (!isset($opts['nocsrf']) && !in_array('nocsrf', $opts)) {
            $html .= $this->_csrfToken;
        }
        $html .= $this->getHTMLFields([], false, $opts, false);
        $html .= $this->_closeForm . $this->_closeColBlock . $this->_closeRowBlock .
            $this->_closeContainerBlock;
        # if specified header in $opts then include includes/header.php
        if (isset($opts['footer']) || in_array('footer', $opts) || isset($opts['footers']) || in_array('footers', $opts)) {
            $html .= getInclude(pathFinder('includes/page_footer.php')) .
                getInclude(pathFinder('includes/html_footer.php'));
        }
        return $this->fillHTML($html, $opts);
    }

    public function getHTMLFields($fieldList=[], $onlyFields=false, $opts=[], $fill=true) {
        $fieldList = $this->_fixFieldList($fieldList, $onlyFields);
        $html = "\n";
        foreach ($fieldList as $f) {
            if (is_object($fld = $this->getField($f))) {
                $html .= $fld->getHTML()."\n";
            } else {
                $html .= $fld; // just additional HTML
            }
        }
        $html .= "\n";
        if ($fill) {
            return $this->fillHTML($html, $opts);
        } else {
            return $html;
        }
    }

    public function getHTMLStart($opts=[]) {
        $html = "";
        # if specified header in $opts then include includes/header.php
        if (isset($opts['header']) || in_array('header', $opts) || isset($opts['headers']) || in_array('headers', $opts)) {
            $html .= getInclude(pathFinder('includes/header.php'));
        }
        $html .= $this->_openContainerBlock . $this->_openRowBlock . $this->_openColBlock .
            $this->_titleBlock;
        # if specified admin in $opts the include admin dashboard
        if (isset($opts['admin']) || in_array('admin', $opts)) {
            $html .= $this->_adminDashboardBlock;
        }
        $html .= $this->_closeColBlock . $this->_closeRowBlock;
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLTitleAndResults($opts=[]) {
        $html = $this->_titleBlock;
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLAdminDashboard($opts=[]) {
        $html = $this->_adminDashboardBlock;
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLOpenRowCol($opts=[]) {
        $html = $this->_openRowBlock . $this->_openColBlock;
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLCloseRowCol($opts=[]) {
        $html = $this->_closeColBlock . $this->_closeRowBlock;
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLOpenForm($opts=[]) {
        $html = $this->_openForm;
        if (!isset($opts['nocsrf'])) {
            $html .= $this->_csrfToken;
        }
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLCloseForm($opts=[]) {
        $html = $this->_closeForm;
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLOpenContainer($opts=[]) {
        $html = $this->_openContainerBlock;
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLCloseContainer($opts=[]) {
        $html = $this->_closeContainerBlock;
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLHeader($opts=[]) {
        $html = getInclude(pathFinder('includes/header.php'));
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLNavigation($opts=[]) {
        $html = getInclude(pathFinder('includes/navigation.php'));
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLPageFooter($opts=[]) {
        $html = getInclude(pathFinder('includes/page_footer.php'));
        return $this->fillHTML($html, $opts);
    }
    public function getHTMLFooter($opts=[]) {
        $html = getInclude(pathFinder('includes/html_footer.php'));
        foreach ($this->getFields() as $f) {
            $html .= $f->getHTMLScripts();
        }
        return $this->fillHTML($html, $opts);
    }

    public function fillHTML($s, $opts) {
        #dbg("fillHTML: $s<br />\n");
        $repl = array_merge($this->_replaces,
                    [
                        '{BROWSER-TITLE}'=>$this->_browserTitle,
                        '{FORM-TITLE}'=>$this->_formTitle,
                    ],
                    (array)@$opts['replaces']);
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
    public function setDBTable($table) {
        $this->_dbTable = $table;
    }
    public function getDBTable() {
        return $this->_dbTable;
    }
	public function setAction($action) {
		$this->_formAction=$action;
	}
    public function getFields($fieldList=[], $onlyFields=true) {
        $fieldList = $this->_fixFieldList($fieldList, $onlyFields);
        $rtn = [];
        foreach ($fieldList as $f) {
            $rtn[$f] = $this->getField($f);
        }
        return $rtn;
    }
    public function getField($fieldName) {
        return $this->_fields[$fieldName];
    }
    public function deleteField($fieldName) {
        unset($this->_fields[$fieldName]);
    }
    public function listFields($onlyFields=true) {
        $rtn = array_keys($this->_fields);
        if ($onlyFields) {
            $rtn = array_filter($rtn, 'is_string'); // get rid of numeric keys (HTML snippets)
        }
        return $rtn;
    }
    public function setFieldValues($vals, $fieldList=array()) {
        $fieldList = $this->_fixFieldList($fieldList);
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
    public function setNewValues($vals, $fieldList=array()) {
        $fieldList = $this->_fixFieldList($fieldList);
        foreach ($fieldList as $f) {
            if (isset($vals[$f])) {
                $this->getField($f)->setNewValue($vals[$f]);
            }
        }
    }
    public function fieldListNewValues($fieldList=array(), $onlyDB=true) {
        $fieldList = $this->_fixFieldList($fieldList);
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
    private function _fixFieldList($fieldList, $onlyFields=true) {
        if ($fieldList) {
            return array_intersect($this->listFields($onlyFields), $fieldList);
        } else {
            return $this->listFields($onlyFields); // o
        }
    }
    public function isChanged($fieldList=array()) {
        $fieldList = $this->_fixFieldList($fieldList);
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

    public function updateIfChangedAndValid($id, &$errors, $fieldList=[]) {
        if (!$table = $this->getDBTable()) {
            $errors[] = 'ERROR: No table specified';
            return false;
        }
        $db = DB::getInstance();
        if ($this->isChanged()) {
            $fields = $this->fieldListNewValues($fieldList, true);
            if ($this->checkFieldValidation($fields, $errors)) {
                if ($db->update($table, $id, $fields)) {
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
	public function checkFieldValidation($data=[], &$errors, $fieldList=array()) {
        $fieldList = $this->_fixFieldList($fieldList);
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
