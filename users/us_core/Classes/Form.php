<?php
/*

*/
class US_Form extends Element {
    const UPDATE_ERROR = 0;
    const UPDATE_SUCCESS = 1;
    const UPDATE_NO_CHANGE = 2;
	protected $_formName,
        $_fields=[],
        $_validateObject=null,
        $_validatePassed=false,
        $defaultFields = [],
        $_keepAdminDashBoard=false, // true for admin pages
        $_hasFieldValues=false, // flag that setFieldValues() was called
        $_dbTableId=null, // which row we load and update
        $_dbAutoLoad=false, // whether we autoload from $_dbTable and $_dbTableId
        $_dbAutoLoadPosted=null, // source (usually $_POST) from which we autoload new values
        $_dbAutoSave=false, // whether we autosave from $_dbTable and $_dbTableId
        # These lang(x) tokens are used in autosave
        $_msgTokenUpdateSuccess='RECORD_UPDATE_SUCCESS',
        $_msgTokenUpdateFailed='RECORD_UPDATE_FAILED',
        $_msgTokenInsertSuccess='RECORD_INSERT_SUCCESS',
        $_msgTokenInsertFailed='RECORD_INSERT_FAILED';
    # These are the elements (each corresponding to $HTML_*) which
    # will be output in getHTML().
    public $elementList = [
        'Header', 'openContainer', 'AdminDashboard',
        'TitleAndResults', 'openForm', 'CSRF', 'openRow', 'openCol',
        'Fields',
        'closeCol', 'closeRow', 'closeForm',
        'PageFooter', 'Footer',
    ];
    public $repElement = 'Fields';
    protected $HTML_openContainer = '
        <div class="container {CONTAINER_CLASS}">'."\n";
    protected $HTML_openRow = '
        <div class="row {ROW_CLASS}">'."\n";
    protected $HTML_openCol = '
        <div class="{COL_CLASS}">'."\n";
    protected $HTML_openForm = '
        <form name="{FORM_NAME}" action="{FORM_ACTION}" method="{FORM_METHOD}">'."\n";
    protected $HTML_CSRF = '
        <input type="hidden" name="csrf" value="{GENERATE_CSRF_TOKEN}">'."\n";
    protected $HTML_closeForm = '
        </form>'."\n";
    protected $HTML_closeContainer = '
        </div> <!-- container {CONTAINER_CLASS} -->'."\n";
    protected $HTML_closeRow = '
        </div> <!-- row {ROW_CLASS} -->'."\n";
    protected $HTML_closeCol = '
        </div> <!-- col {COL_CLASS} -->'."\n";
    protected $HTML_Title = '
        <div class="{COL_CLASS}"> <!-- title col -->
            <h2>{FORM_TITLE}</h2>
        </div> <!-- title col -->
        ';
    protected $HTML_Well_Title = '
        <div class="{COL_CLASS}"> <!-- well title col -->
            <h2>{FORM_TITLE}</h2>
        </div> <!-- well title col -->
        ';
    protected $HTML_TitleAndResults = '
        <div class="{COL_CLASS}"> <!-- title & results col -->
            <h2>{FORM_TITLE}</h2>
    		{RESULT_BLOCK}
        </div> <!-- title & result col -->
        ';
    protected $HTML_AdminDashboard = '
        <div class="xs-col-12"> <!-- admin dashboard col -->
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
        if (@$opts['default_everything']) {
            if (isset($opts['dbtable'])) {
                $table = $opts['dbtable'];
            } elseif (isset($opts['table'])) {
                $table = $opts['table'];
            } else {
                dbg("FATAL ERROR: Must set dbtable for default_everything");
                exit;
            }
            $this->setDBTable($table);
            $this->calcDefaultFields();
            $this->setDbAutoLoad(true);
            $this->setDbAutoLoadPosted($_POST);
            $this->setDbAutoSave(true);
            $fields = array_merge($this->defaultFields, $fields);
            unset($opts['default_everything']);
        }
        $opts = array_merge([$this->repElement=>$fields], $opts);
        parent::__construct($opts);
        foreach ($opts[$this->repElement] as $f => &$v) {
            $v->setDefaults($f);
        }
        // $formName is usually set prior to master_form.php
        if (!$this->_formName && @$GLOBALS['formName']) {
            $this->_formName = $GLOBALS['formName'];
        }
        if (!$this->getMacro('Form_Title')) {
            $this->setTitleByPage();
        }
        // delete conditional fields or sub-forms (keep_if or delete_if logic in $opts)
        $this->checkFields2Delete();
        if (!$this->getKeepAdminDashBoard()) {
            $this->deleteElements('AdminDashboard');
        }
        if ($this->getDbAutoLoadPosted()) {
            dbg("Loading posted values");
            $this->setNewValues($this->getDbAutoLoadPosted());
        }
        if ($this->getDBTable()) {
            if ($this->getDbAutoLoad()) {
                $this->dbAutoLoad();
            }
            if ($this->getDbAutoSave()) {
                $this->dbAutoSave();
                # Now load the values that were just saved
                if ($this->getDbAutoLoad()) {
                    $this->dbAutoLoad();
                }
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
            case 'active_tab':
                $this->setTabIsActive($val);
                return true;
                break;
            case 'tab_id':
                $this->setTabId($val);
                return true;
                break;
            case 'keep_admindashboard':
                $this->setKeepAdminDashBoard($val);
                return true;
                break;
            case 'tableid':
            case 'dbtableid':
                $this->setDbTableId($val);
                return true;
                break;
            case 'autoload':
            case 'dbautoload':
                $this->setDbAutoLoad($val);
                return true;
                break;
            case 'autosave':
            case 'dbautosave':
                $this->setDbAutoSave($val);
                return true;
                break;
            case 'autoloadposted':
            case 'autoloadnew':
                $this->setDbAutoLoadPosted($val);
                return true;
                break;
            case 'update_success_message':
                $this->_msgTokenUpdateSuccess = $val;
                return true;
                break;
            case 'update_failed_message':
                $this->_msgTokenUpdateFailed = $val;
                return true;
                break;
            case 'insert_success_message':
                $this->_msgTokenInsertSuccess = $val;
                return true;
                break;
            case 'insert_failed_message':
                $this->_msgTokenInsertFailed = $val;
                return true;
                break;
        }
        if (parent::handle1Opt($name, $val)) {
            return true;
        }
        return false;
    }
    public function calcDefaultFields() {
        dbg("Gotta figure a way so \$opts isn't dependent on its order");
        if (!isset($T[$this->getDbTable()])) {
            $T[$this->getDbTable()] = $this->getDbTable();
        }
        $db = DB::getInstance(); // $this->_db not available yet
        $fields = $db->query("SHOW COLUMNS FROM {$T[$this->getDbTable()]}")->results();
        foreach ($fields as $f) {
            if ($f->Field == 'id') {
                continue;
            }
            if (($i = strpos($f->Type, '(')) === false) {
                $type = $f->Type;
            } else {
                $type = substr($f->Type, 0, $i);
            }
            $fn = $f->Field;
            #var_dump($f);
            #dbg("name={$f->Field}, type=$type ({$f->Type})");
            if ($f->Type == 'tinyint(1)') { // boolean
                $defs[$fn] = new FormField_Checkbox;
            } else {
                # later on we can get fancy with dates and stuff
                $defs[$fn] = new FormField_Text;
            }
        }
        $defs['save'] = new FormField_ButtonSubmit;
        $this->defaultFields = $defs;
    }
    public function checkFields2Delete($recursive=false) {
        $this->debug(2,"::checkFields2Delete(".($recursive?"TRUE":"FALSE")."): Entering");
        foreach ($this->getFields([], ['not_only_fields']) as $fieldName=>$f) {
            $this->debug(4,"::checkFields2Delete() Checking $fieldName");
            if ((method_exists($f, 'getDeleteMe') && $f->getDeleteMe())) {
                $this->debug(5,"Deleting field=$fieldName");
                $this->deleteField($fieldName);
            } elseif ($recursive && method_exists($f, 'checkFields2Delete')) {
                $this->debug(5,"calling checkFields2Delete for $fieldName");
                $f->checkFields2Delete($recursive);
            }
        }
    }
    public function checkDeleteIfEmpty($recursive=true) {
        $this->debug(2,"::checkDeleteIfEmpty(".($recursive?"TRUE":"FALSE")."): Entering");
        foreach ($this->getFields([], ['not_only_fields']) as $fieldName=>$f) {
            $this->debug(4,"::checkDeleteIfEmpty() top-of-loop Checking $fieldName");
            if ((method_exists($f, 'getDeleteIfEmpty') &&
                    method_exists($f, 'repDataIsEmpty') &&
                    $f->getDeleteIfEmpty() && $f->repDataIsEmpty($recursive))) {
                $this->debug(4,"Deleting field=$fieldName");
                $this->deleteField($fieldName);
            } elseif ($recursive && method_exists($f, 'checkDeleteIfEmpty')) {
                $this->debug(4,"calling checkDeleteIfEmpty for $fieldName");
                $f->checkDeleteIfEmpty($recursive);
            }
        }
    }
    public function dbAutoLoad() {
        #dbg("Form::dbAutoLoad(): Entering");
        if (!$this->getDBTableId() && @$_GET['id']) {
            $this->setDBTableId($_GET['id']);
        }
        $dbData = $this->_db->findById($this->getDBTable(), $this->getDBTableId());
        if ($dbData) {
            #dbg("Form::dbAutoLoad(): found dbData record");
            return $this->setFieldValues($dbData->first());
        } else {
            #dbg("AutoLoad failure - no row matching id={$this->_dbTableId} for table={$this->_dbTable}");
            return false;
        }
    }
    public function dbAutoSave() {
        #dbg("Form::dbAutoSave(): Entering");
        $this->setNewValues($_POST);
        if ($this->getDBTableId()) { // update existing row
            if ($this->_updateIfValid()) {
                $this->successes[] = lang($this->_msgTokenUpdateSuccess);
                return true;
            } else {
                $this->errors[] = lang($this->_msgTokenUpdateFailed);
                return false;
            }
        } else { // insert new row
            if ($this->_insertIfValid()) {
                $this->successes[] = lang($this->_msgTokenInsertSuccess);
                return true;
            } else {
                $this->errors[] = lang($this->_msgTokenInsertFailed);
                return false;
            }
        }
    }
    public function setTitleByPage() {
        global $T;
        $page = $this->_db->query("SELECT * FROM $T[pages] pages WHERE page = ?", [$this->_formName]);
        if ($page->count() > 0) {
            $pageRow = $page->first();
            $this->setMacro('Form_Title', lang($pageRow->title_lang));
        }
    }

    public function setDefaults($k) {
        // do nothing in forms - it is handled by the construct so no need for recursion
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
        foreach ($this->getAllFields() as $f=>$field) {
            if (method_exists($field, 'getHTMLScripts')) {
                $html = $field->getHTMLScripts().$html;
            }
        }
        return $html;
    }

    public function getMacros($s, $opts) {
        $macros = parent::getMacros($s, $opts);
        # These macros are "expensive" to evaluate and so are only
        # evaluated if they actually exist in the $s string
        if (stripos($s, "{RESULT_BLOCK}") !== false) {
            $errors = isset($opts['errors']) ? $opts['errors'] : $this->errors;
            $successes = isset($opts['successes']) ? $opts['successes'] : $this->successes;
            $macros['{RESULT_BLOCK}'] = ResultBlock((array)$errors, (array)$successes);
        }
        if (stripos($s, "{GENERATE_CSRF_TOKEN}") !== false) {
            $macros['{GENERATE_CSRF_TOKEN}'] = Token::generate();
        }
        if (stripos($s, "{INCLUDE_ADMIN_DASHBOARD}") !== false) {
            $macros['{INCLUDE_ADMIN_DASHBOARD}'] = getInclude(pathFinder('includes/admin_dashboard.php'));
        }
        return $macros;
    }

    public function getDbAutoSave() {
        return (Input::exists() && $this->_dbAutoSave);
    }
    public function setDbAutoSave($val) {
        $this->_dbAutoSave = $val;
    }
    public function getDbAutoLoadPosted() {
        return $this->_dbAutoLoadPosted;
    }
    public function setDbAutoLoadPosted($val) {
        if ($val) {
            if (is_array($val)) {
                $this->_dbAutoLoadPosted = $val;
            } else {
                $this->_dbAutoLoadPosted = $_POST;
            }
        } else {
            $this->_dbAutoLoadPosted = $val;
        }
    }
    public function getDbAutoLoad() {
        return $this->_dbAutoLoad;
    }
    public function setDbAutoLoad($val) {
        $this->_dbAutoLoad = $val;
    }
    public function getDbTableId() {
        return $this->_dbTableId;
    }
    public function setDbTableId($val) {
        $this->_dbTableId = $val;
    }
    public function setTabIsActive($val) {
        $this->MACRO_Tab_Pane_Active = $val;
    }
    public function setTabId($val) {
        $this->MACRO_Tab_Id = $val;
    }
	public function setAction($action) {
		$this->MACRO_Form_Action=$action;
	}
    public function setKeepAdminDashBoard($val) {
        $this->_keepAdminDashBoard = $val;
    }
    public function getKeepAdminDashBoard() {
        return $this->_keepAdminDashBoard;
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
        $fieldList = $this->fixFieldList($fieldFilter, $onlyFields);
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
        $rtn = [];
        foreach ($this->repData as $k=>$v) {
            if (!$onlyFields || is_a($v, 'US_FormField')) {
                $rtn[] = $k;
            }
            if (method_exists($v, 'listFields')) {
                $rtn = array_merge($rtn, $v->listFields($onlyFields));
            }
        }
        #dbg("listFields(): Returning <pre>".print_r($rtn,true)."</pre><br />");
        return $rtn;
    }
    public function setFieldValues($vals, $fieldFilter=array()) {
        $this->debug(1, '::setFieldValues(): Entering');
        $fieldList = $this->fixFieldList($fieldFilter);
        foreach ($fieldList as $f) {
            $this->debug(2, "::setFieldValues() - looping with f=$f");
            if (is_array($vals)) {
                if (isset($vals[$f])) {
                    $this->getField($f)->setFieldValue($vals[$f]);
                    $this->debug(3, "::setFieldValues(): using array to set ".print_r($vals[$f],true));
                }
            } else { # presumably it is an object
                $curObj = $this->getField($f);
                // handle nested forms (like for tabs)
                if (method_exists($curObj, 'setFieldValues')) {
                    $this->debug(3, "::setFieldValues(): using curObj to call setFieldValues()");
                    $curObj->setFieldValues($vals, $fieldFilter);
                } elseif (isset($vals->$f)) {
                    #if (!method_exists($curObj, 'setFieldValue')) { dbg( 'class='.get_class($curObj).', parent='.get_parent_class($curObj)); var_dump($curObj); }
                    $this->debug(3, "::setFieldValues(): using curObj to set ".print_r($vals->$f,true));
                    $curObj->setFieldValue($vals->$f);
                }
            }
        }
        $this->_hasFieldValues = true; // mark it as loaded
    }
    public function setNewValues($vals, $fieldFilter=array()) {
        $fieldList = $this->fixFieldList($fieldFilter);
        foreach ($fieldList as $f) {
            if (isset($vals[$f])) {
                $this->getField($f)->setNewValue($vals[$f]);
            }
        }
    }
    public function fieldListNewValues($fieldFilter=array(), $onlyDB=true) {
        $fieldList = $this->fixFieldList($fieldFilter);
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
    protected function fixFieldList($fieldFilter, $onlyFields=true) {
        if ($fieldFilter) {
            return array_intersect($this->listFields($onlyFields), $fieldFilter);
        } else {
            return $this->listFields($onlyFields);
        }
    }
    public function isChanged($fieldFilter=array()) {
        $fieldList = $this->fixFieldList($fieldFilter);
        foreach ($fieldList as $f) {
            if ($this->getField($f)->isChanged()) {
                return true;
            }
        }
        return false;
    }
    public function repDataIsEmpty($recursive=false) {
        if (!$recursive) {
            return !(boolean)$this->repData;
        }
        $isEmpty = false;
        foreach ($this->repData as $k=>$r) {
            $this->debug(2, "::repDataIsEmpty(): k=$k");
            if (!method_exists($r, 'isRepeating') || !$r->isRepeating()) {
                continue;
            }
            if (method_exists($r, 'repDataIsEmpty')) {
                if ($r->repDataIsEmpty($recursive)) {
                    $isEmpty = true;
                } else {
                    $isEmpty = false;
                    break;
                }
            }
        }
        return $isEmpty;
    }

	public function getFormName() {
		return $this->_formName;
	}
	public function setFormName($name) {
		$this->_formName=$name;
	}

    public function insertIfValid(&$errors, $fieldFilter=[]) {
        if (!$table = $this->getDBTable()) {
            $errors[] = 'ERROR: No table specified';
            return false;
        }
        $this->errors = &$errors;
        return _insertIfValid($fieldFilter);
    }
    public function _insertIfValid($fieldFilter=[]) {
        $fields = $this->fieldListNewValues($fieldFilter, true);
        #var_dump($fields);
        #var_dump($_POST);
        if ($this->checkFieldValidation($fields, $this->errors)) {
            if ($this->_db->insert($this->getDbTable(), $fields)) {
                return $this->_db->lastId();
            } else {
                $this->errors[] = lang('SQL_ERROR');
                return false;
            }
        } else {
            return false;
        }
    }

    public function updateIfValid($id, &$errors, $fieldFilter=[]) {
        if (!$table = $this->getDBTable()) {
            $errors[] = 'ERROR: No table specified';
            return false;
        }
        if ($this->isChanged()) {
            $this->errors = &$errors;
            $this->setDbTableId($id);
            return $this->_updateIfValid($fieldFilter);
        }
        return self::UPDATE_NO_CHANGE; // means no error, but no update occurred
    }
    protected function _updateIfValid($fieldFilter=[]) {
        $fields = $this->fieldListNewValues($fieldFilter, true);
        if ($this->checkFieldValidation($fields, $this->errors)) {
            if ($this->_db->update($this->getDbTable(), $this->getDbTableId(), $fields)) {
                return self::UPDATE_SUCCESS; // means update occurred
            } else {
                $this->errors[] = lang('SQL_ERROR');
                return self::UPDATE_ERROR; // error return value
            }
        } else {
            return self::UPDATE_ERROR;
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
        $fieldList = $this->fixFieldList($fieldFilter);
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
}
