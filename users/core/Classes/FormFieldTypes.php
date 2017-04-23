<?php
/*
 * See the detailed description of the inheritance structure related to
 * the class "FormField" which is provided in the header comment in
 * core/Classes/FormField.php.
 *
 * In short,
 *    - do not change this file (it is under core/ - don't ever change
 *      anything under core/)
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
} /* Button */
abstract class US_FormField_ButtonAnchor extends FormField_Button {
    protected $_fieldType = "button";
    public $HTML_Input = '
            <a href="{LINK}" class="{INPUT_CLASS}" type="{TYPE}"><span class="{BUTTON_ICON}"></span> {LABEL_TEXT}</a>
            ',
        $MACRO_Link = '';
    public function handle1Opt($name, &$val) {
        if (in_array(strtolower($name), ['href', 'link', 'dest'])) {
            $this->MACRO_Link = $val;
            return true;
        }
        return parent::handle1Opt($name, $val);
    }
} /* ButtonAnchor */
abstract class US_FormField_ButtonSubmit extends FormField_Button {
} /* ButtonSubmit */
abstract class US_FormField_ButtonDelete extends FormField_Button {
    public $MACRO_Input_Class = 'btn btn-primary btn-danger';
} /* ButtonDelete */

abstract class US_FormField_Checkbox extends FormField {
    protected $_fieldType = "checkbox";
    protected $checked = 'checked';
    public $MACRO_Checked = '';
	public $HTML_Pre =
            '<div class="{DIV_CLASS}"> <!-- checkbox -->
            ',
        $HTML_Input =
    		'<input type="hidden" name="{FIELD_NAME}" value="0" />
             <input type="{TYPE}" name="{FIELD_NAME}" id="{FIELD_ID}" value="1" {CHECKED} >
            ',
        $HTML_Post =
		    '<label class="{LABEL_CLASS}" for="{FIELD_ID}">{LABEL_TEXT}</label>
        	 </div> <!-- {DIV_CLASS} (checkbox name={FIELD_NAME}, id={FIELD_ID}) -->
             ';
    public function getMacros($s, $opts) {
        $macros = parent::getMacros($s, $opts);
        $fv = $this->getFieldValue();
        if ($fv) {
            $macros['{Checked}'] = $this->checked;
        } else  {
            $macros['{Checked}'] = '';
        }
        return $macros;
    }
} /* Checkbox */

abstract class US_FormField_Checklist extends FormField {
    protected $_fieldType = "checkbox";
    protected $_isDBField = false;
    /* Array can be ($_indexBy=='id') indexed by id and value contains 1 or 0 or it can be
     * ($_indexBy=='seq') an arbitrary sequential-from-0 index and contain the id in the value.
     */
    protected $_indexBy = 'seq'; // arbitrary (sequential from 0) index - value will be id; 'id' is alternative setting
    public $elementList = ['Input', 'Post'], // no Pre or Post
        $HTML_Pre = '', # you can set this to '<br /> or something if desired'
        $HTML_Input = '', # this will be set to either checkboxIndexById or $...Seq depending on $_indexBy
        $HTML_Post = '{FOOTER}',
        $checkboxIndexById = '<label class="{LABEL_CLASS}"><input type="{TYPE}" name="{COLUMN_NAME_PREFIX}{NAME}[{ID}]" value="1">{INTER_SPACE}{COLUMN_VALUE}</label>{SEPARATOR}',
        $checkboxIndexBySeq = '<label class="{LABEL_CLASS}"><input type="{TYPE}" name="{COLUMN_NAME_PREFIX}{NAME}[]" value="{ID}">{INTER_SPACE}{COLUMN_VALUE}</label>{SEPARATOR}';
    public $repMacroAliases = ['ID', 'COLUMN_VALUE'],
        $repElement = 'HTML_Input',
        $MACRO_Column_Name_Prefix = '',
        $MACRO_Footer='<br />',
        $MACRO_Inter_Space=' ', // between the checkbox and the label
        $MACRO_Separator='<br />'; // you might want to set this to str_repeat('&nbsp;', 5) or something for more horizontally oriented checklist

    public function handleOpts($opts=[]) {
        $rtn = parent::handleOpts($opts);
        if ($this->_indexBy == 'id') {
            $this->HTML_Input = $this->checkboxIndexById;
        } else { // presumably 'seq'
            $this->HTML_Input = $this->checkboxIndexBySeq;
        }
        #dbg('<pre>'.htmlentities($this->HTML_Input).'</pre>');
        return $rtn;
    }
    public function handle1Opt($name, &$val) {
        switch (strtolower(str_replace('_', '', $name))) {
            case 'prefix':
                $this->setMacro('Column_Name_Prefix', $val);
                return true;
            case 'separator':
            case 'sep':
                $this->setMacro('Separator', $val);
                return true;
            case 'foot':
            case 'footer':
                $this->setMacro('Footer', $val);
                return true;
            case 'indexby':
                $this->_indexBy = $val;
                return true;
        }
        return parent::handle1Opt($name, $val);
    }
} /* Checklist */

abstract class US_FormField_File extends FormField {
    protected $_fieldType = "file";
    protected $_isDBField = false; // unlikely that a file will be stored in the DB
    protected $_rename = "", // empty string means don't rename
        $_uploadDir="", // destination directory for uploads
        $_required=false, // is this upload required
        $_allowedExt=[], // allowed extensions (i.e., jpg,png,gif)
        $_maxSize=null, // maximum size
        $_allowOverwrite = false; // if file exists do we overwrite?
    public $elementList = ['Pre', 'Input', 'Post'], // Pre and Post come from FormField
        $HTML_Input = '
            <input type="hidden" name="MAX_FILE_SIZE" value="{MAX_FILE_SIZE}"/>
            <input type="{TYPE}" class="{INPUT_CLASS}" id="{FIELD_ID}" name="{FIELD_NAME}" {DISABLED} {READONLY} />
            ';
    public $MACRO_Max_File_Size = -1;

    public function __construct($opts=[], $processor=[]) {
        # Set some appropriate defaults that can be over-ridden by parent::__construct()
        $this->setMaxSize(configGet('upload_max_size', 1));
        $this->setUploadDir(configGet('upload_dir', US_ROOT_DIR."uploads"));
        $this->setAllowedExt(configGet('upload_allowed_ext'));
        parent::__construct($opts, $processor);
    }
    public function handleOpts($opts=[]) {
        $rtn = parent::handleOpts($opts);
        # If the dev didn't already set up validation then set it up here
        # (normally dev should just specify the options and let us set it up here)
        if (is_null($this->_validateObject)) {
            $this->setValidator([
                'upload_max_size' => $this->getMaxSize(),
                'upload_ext' => $this->getAllowedExt(),
                'required' => $this->_required,
                'upload_errs' => true,
            ]);
        }
        #dbg("Setting max to ".$this->getMaxSize());
        $this->setMacro('Max_File_Size', $this->getMaxSize());
        return $rtn;
    }
    public function handle1Opt($name, &$val) {
        switch (strtolower(str_replace('_', '', $name))) {
            case 'maxfilesize':
            case 'maxuploadsize':
            case 'uploadmaxsize':
                $this->setMaxSize($val);
                return true;
            case 'ext':
            case 'extension':
            case 'allowedextension':
            case 'uploadext':
                $this->setAllowedExt($val);
                return true;
            case 'uploaddir':
            case 'dir':
                $this->setUploadDir($val);
                return true;
            case 'overwrite':
            case 'allowoverwrite':
                $this->_allowOverwrite = $val;
                return true;
            case 'required':
                $this->_required = $val;
                return true;
        }
        return parent::handle1Opt($name, $val);
    }
    public function getMaxSize() {
        return $this->_maxSize;
    }
    public function setMaxSize($val) {
        $this->_maxSize = $val;
    }
    public function setUploadDir($val) {
        $this->_uploadDir = $val;
    }
    public function setAllowedExt($val) {
        if (is_array($val)) {
            $this->_allowedExt = $val;
        } elseif (empty($val)) {
            $this->_allowedExt = [];
        } else {
            $this->_allowedExt = preg_split('/[,|\s]+/', trim($val), PREG_SPLIT_NO_EMPTY);
        }
    }
    public function getAllowedExt() {
        return $this->_allowedExt;
    }
    public function saveUpload() {
        $myFile = $_FILES[$this->getFieldName()];
        if ($myFile['error'] == UPLOAD_ERR_OK) {
            $uploadDir = $this->getUploadDir();
            if (!$uploadDir) {
                $this->errors[] = lang("UPLOAD_DIR_NOT_SET");
                return false;
            }
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir); // take a shot at creating it
            }
            if (!file_exists($uploadDir)) {
                $this->errors[] = lang("UPLOAD_DIR_NONEXIST");
                return false;
            } else {
                $fullDest = $uploadDir.$myFile['name'];
                if (file_exists($fullDest) && !$this->getAllowOverwrite()) {
                    $this->errors[] = lang("UPLOAD_FILE_EXISTS_NO_OVERWRITE");
                } else {
                    move_uploaded_file($myFile['tmp_name'], $fullDest);
                    $this->setFieldValue($fullDest);
                    $this->successes[] = lang('UPLOAD_SUCCESS', $fullDest);
                }
            }
        }
    }
    public function getUploadDir() {
        if ($this->_uploadDir) {
            return $this->_uploadDir;
        } else {
            return configGet('upload_dir', US_ROOT_DIR.'uploads');
        }
    }
    public function dataIsValid($data=null) {
        if ($this->hasValidation()) {
            if (!$data) {
                $data = $_FILES;
            }
            return $this->getValidator()->check($data)->passed();
        } else {
            return true; // if no validation then it cannot fail
        }
    }
    public function getAllowOverwrite() {
        return $this->_allowOverwrite;
    }
} /* File (upload) */

abstract class US_FormField_Hidden extends FormField {
    protected $_fieldType = "hidden";
    public $elementList = ['Input'], // no Pre or Post
        $HTML_Input = '
            <input type="{TYPE}" name="{FIELD_NAME}" value="{VALUE}">
            ';
} /* Hidden */

abstract class US_FormField_HTML extends FormField {
    public $_isDBField = false,
        $HTML_Input = '{VALUE}';
} /* HTML */

abstract class US_FormField_MultiHidden extends FormField {
    protected $_fieldType = "hidden";
    public $elementList = ['Input'], // no Pre or Post
        $HTML_Input = '<input type="{TYPE}" name="{COLUMN_NAME_PREFIX}{COLUMN_NAME}[{ID}]" value="{COLUMN_VALUE}">
            ';
        #$_dataFields = [],
        #$_dataFieldLabels = [];
    public $repMacroAliases = ['ID', 'COLUMN_NAME'],
        $repElement = 'HTML_Input',
        $MACRO_Column_Name_Prefix = '';

    public function handle1Opt($name, &$val) {
        switch (strtolower(str_replace('_', '', $name))) {
            case 'hiddencols':
                $saveInput = $this->HTML_Input;
                $this->HTML_Input = '';
                foreach ($val as $v) {
                    if ($v == 'id') continue;
                    $this->HTML_Input .= str_replace(
                        ['{COLUMN_NAME}','{COLUMN_VALUE}'],
                        [$v, '{'.$v.'}'], $saveInput);
                }
                #dbg("HIDDEN INPUT: ".htmlentities($this->HTML_Input));
                return true;
            case 'prefix':
                $this->setMacro('Column_Name_Prefix', $val);
                return true;
        }
        return parent::handle1Opt($name, $val);
    }
} /* MultiHidden */

abstract class US_FormField_Password extends FormField {
    protected $_fieldType = "password";
    public $HTML_Script = [
        '<script type="text/javascript" src="'.US_URL_ROOT.'resources/js/zxcvbn.js"></script>',
        '<script type="text/javascript" src="'.US_URL_ROOT.'resources/js/zxcvbn-bootstrap-strength-meter.js"></script>',
    ];
    public function handle1Opt($name, &$val) {
        switch (strtolower(str_replace('_', '', $name))) {
            case 'passwordmeter':
            case 'strengthmeter':
            case 'pwmeter':
                if ($val) {
                    $this->HTML_Script[] =
                        '<script type="text/javascript">
                        	$(document).ready(function () {
                        		$("#{FIELD_ID}-StrengthProgressBar").zxcvbnProgressBar({ passwordInput: "#{FIELD_ID}" });
                        	});
                        </script>';
                    $this->HTML_Post .=
                        '<div class="progress">
                        	<div id="{FIELD_ID}-StrengthProgressBar" class="progress-bar"></div>
                         </div>';
                }
                return true;
        }
        return parent::handle1Opt($name, $val);
    }
} /* Password */

abstract class US_FormField_Radio extends FormField {
    protected $_fieldType = "radio";
    public
        $HTML_Pre = '
            <div class="{DIV_CLASS}"> <!-- Radio (id={FIELD_ID}, name={FIELD_NAME}) -->
            <label class="{LABEL_CLASS}" for="{FIELD_ID}">{LABEL_TEXT}
            <span class="{HINT_CLASS}" title="{HINT_TEXT}"></span></label>
            ',
        $HTML_Input = '
            <div class="radio">
				<label for="{FIELD_ID}-{ID}" class="{LABEL_CLASS}">
					<input type="{TYPE}" name="{FIELD_NAME}" id="{FIELD_ID}-{ID}" class="{INPUT_CLASS}" value="{ID}">
					{OPTION_LABEL}
				</label>
			</div> <!-- radio -->
            ',
        $HTML_Post = '
            </div> <!-- {DIV_CLASS} Radio (id={FIELD_ID}, name={FIELD_NAME}) -->
            ',
        $repElement = 'HTML_Input';
} /* Radio */

abstract class US_FormField_Recaptcha extends FormField {
    protected $_fieldType = "recaptcha"; // not used
    protected $_validateErrors = [];
    public $MACRO_Recaptcha_Class = 'g-recaptcha',
        $MACRO_Recaptcha_Public = '';
    public $HTML_Pre = '
            <div class="{DIV_CLASS}"> <!-- recaptcha -->
    		<label class="{LABEL_CLASS}">{LABEL_TEXT}</label>
             ',
        $HTML_Input = '
            <div class="{RECAPTCHA_CLASS}" name="{RECAPTCHA_PUBLIC}"></div>
            ',
        $HTML_Post = '
            </div> <!-- {DIV_CLASS} recaptcha -->
            ',
        $HTML_Script = '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js" async defer></script>';
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
        return parent::getMacros($s, $opts);
    }
} /* Recaptcha */

abstract class US_FormField_SearchQ extends FormField {
    public
        $HTML_Pre = '
            <div class="input-group col-xs-12"> <!-- SearchQ -->
            <!-- USE TWITTER TYPEAHEAD JSON WITH API TO SEARCH -->
            ',
        $HTML_Input = '
            <input class="{INPUT_CLASS}" id="{FIELD_ID}" name="{FIELD_NAME}" placeholder="{PLACEHOLDER}" {REQUIRED_ATTRIB}>
            <span class="input-group-btn">
              <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
            </span>
			<div class="searchQinfo">&nbsp;</div>
            ',
        $HTML_Post = '
            </div> <!-- SearchQ -->
            ',
        $HTML_Script = '<script type="text/javascript" src="'.US_URL_ROOT.'resources/js/search.js" charset="utf-8"></script>';
    public $MACRO_Field_Id = 'system-search',
        $MACRO_Field_Name = 'q',
        $MACRO_Placeholder = 'Search Text...';
    protected $_isDBField = false;
} /* SearchQ */

abstract class US_FormField_Select extends FormField {
    protected $_fieldType = "select";
    public $MACRO_Selected = '';
    public $idField = 'id';
    public $repMacroAliases = ['OPTION_VALUE', 'OPTION_LABEL'];
    public
        $HTML_Pre = '
            <div class="{DIV_CLASS}"> <!-- Select (name={FIELD_NAME}, id={FIELD_ID}) -->
            <label class="{LABEL_CLASS}" for="{FIELD_ID}">{LABEL_TEXT}
            <span class="{HINT_CLASS}" title="{HINT_TEXT}"></span></label>
            <br />
            <select class="{INPUT_CLASS}" id="{FIELD_ID}" name="{FIELD_NAME}" {DISABLED}>
            ',
        $HTML_Input = '
            <option value="{OPTION_VALUE}" {SELECTED}>{OPTION_LABEL}</option>
            ',
        $HTML_Post = '
            </select>
            </div> <!-- {DIV_CLASS} Select (id={FIELD_ID}, name={FIELD_NAME}) -->
            ',
        $repElement = 'HTML_Input';
    protected
        $placeholderRow = [],
        $selected = 'selected="selected"';
    public function handle1Opt($name, &$val) {
        switch (strtolower(str_replace('_', '', $name))) {
            case 'placeholderrow':
                $this->setPlaceholderRow($val);
                return true;
            case 'idfield':
                $this->setIdField($val);
                return true;
        }
        return parent::handle1Opt($name, $val);
    }
    public function setPlaceholderRow($v) {
        $this->placeholderRow = $v;
    }
    public function repDataIsEmpty($considerPlaceholder=false) {
        return (!(boolean)$this->repData &&
            (!$considerPlaceholder || !(boolean)$this->placeholderRow));
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
        #if (!@$row[$this->getIdField()]) { dbg("id field=".$this->getIdField().", row follows"); var_dump($row); }
        $rowVal = $row[$this->getIdField()];
        #dbg("specialRowMacros: Comparing rowVal=$rowVal to fv=$fv");
        if (($fv === $rowVal) ||
                ($fv !== 0 && $rowVal !== 0 && $fv == $rowVal) ||
                ($fv === '0' && $rowVal === 0) ||
                ($fv === 0 && $rowVal === '0')) {
            #dbg("MATCH!");
            $macros['{SELECTED}'] = $this->selected;
        } else {
            #dbg("NO MATCH!");
            #var_dump($fv);
            #var_dump($rowVal);
            $macros['{SELECTED}'] = '';
        }
    }
    public function getMacros($s, $opts) {
        if (!$this->MACRO_Hint_Text) {
            $this->MACRO_Hint_Text = lang('CHOOSE_FROM_LIST_BELOW');
        }
        return parent::getMacros($s, $opts);
    }
    public function getIdField() {
        return $this->idField;
    }
    public function setIdField($val) {
        $this->idField = $val;
    }
} /* Select */

abstract class US_FormField_Table extends FormField {
    protected $_fieldType = "table",
        $_dataFields = [],
        $_dataFieldLabels = [],
        $selectOptions = [],
        $multiCheckboxes = [];
    public $repMacroAliases = ['ID', 'NAME'];
    public
        $MACRO_Table_Class = "table-hover",
        $MACRO_TH_Row_Class = "",
        $MACRO_TH_Cell_Class = "",
        $MACRO_TD_Row_Class = "",
        $MACRO_TD_Cell_Class = "",
        $MACRO_Checkbox_Label = "";
    public
        $HTML_Pre = '
            <div class="{DIV_CLASS}"> <!-- Table (name={FIELD_NAME}) -->
            <table class="table {TABLE_CLASS}">
            <thead>
            <tr class="{TH_ROW_CLASS}">{TABLE_HEAD_CELLS}</tr>
            </thead>
            <tbody>
            ',
        $HTML_Input = '
            <tr class="{TD_ROW_CLASS}">{TABLE_DATA_CELLS}</tr>
            ',
        $HTML_Post = '
            </tbody>
            </table>
            {PAGE_INDEX}
            </div> <!-- {DIV_CLASS} Table (name={FIELD_NAME}) -->
            ',
        $HTML_Checkallbox = '<label><input type="checkbox" id="checkall-{FIELD_NAME}" />{LABEL_TEXT}</label>',
        $HTML_Checkbox_Id = '<input type="checkbox" name="{FIELD_NAME}[]" id="{FIELD_NAME}-{ID}" value="{ID}"/><label class="{LABEL_CLASS}" for="{FIELD_NAME}-{ID}">&nbsp;{CHECKBOX_LABEL}</label>',
        $HTML_Checkbox_Value = '<input type="checkbox" name="{FIELD_NAME}[{ID}]" id="{FIELD_NAME}-{ID}" value="{VALUE}"/><label class="{LABEL_CLASS}" for="{FIELD_NAME}-{ID}">&nbsp;{CHECKBOX_LABEL}</label>',
        $HTML_Hidden_Id = '<input type="hidden" name="{FIELD_NAME}[{ID}]" id="{FIELD_NAME}-{ID}" value="{ID}"/>',
        $HTML_Fields = [
            'text' => '<input type="text" name="{FIELD_NAME}[{ID}]" id="{FIELD_NAME}-{ID}" value="{{FIELD_NAME}}"/>',
            'hidden' => '<input type="hidden" name="{FIELD_NAME}[{ID}]" id="{FIELD_NAME}-{ID}" value="{{FIELD_NAME}}"/>',
            'checkbox' => '<input type="hidden" name="{FIELD_NAME}[{ID}]" value="0" /><input type="checkbox" name="{FIELD_NAME}[{ID}]" id="{FIELD_NAME}-{ID}" value="1" {CHECKED}/><label class="{LABEL_CLASS}" for="{FIELD_NAME}-{ID}">&nbsp;{CHECKBOX_LABEL}</label>',
            'select' => 'GOOD LUCK!',
        ],
        $HTML_Checkall_Script = '<script type="text/javascript" src="'.US_URL_ROOT.'resources/js/jquery-check-all.min.js"></script>',
        $HTML_Checkall_Init = '<script>$("#checkall-{FIELD_NAME}").checkAll({ childCheckBoxes:"{FIELD_NAME}", showIndeterminate:true });</script>',
        $repElement = 'HTML_Input';

    public function handle1Opt($name, &$val) {
        # this goes above the switch because otherwise underscores
        # in the field name get lost
        if (preg_match('/^select\((.*)\)$/', $name, $m)) {
            $this->selectOptions[$m[1]] = $val;
            return true;
        }
        $simpleName = strtolower(str_replace('_', '', $name));
        switch ($simpleName) {
            case 'tabledatacells':
            case 'tdrow':
                #dbg('setting table_data_cells');
                if (is_array($val)) {
                    $val = '<td>'.implode('</td><td>', $val).'</td>';
                }
                preg_match_all('/{([a-z_][a-z_0-9]*)\((text|hidden|checkbox)(-?seq)?(?:,\s*([^()]*))?\)}/i', $val, $m, PREG_SET_ORDER);
                #var_dump($m);
                #var_dump($val);
                foreach ($m as $x) {
                    $repl = [ '{FIELD_NAME}' => $x[1], '{LABEL}' => @$x[4], ];
                    if (isset($x[3]) && $x[3]) {
                        $repl['{ID}']='{SEQ}';
                    }
                    if (strtolower($x[2]) == 'checkbox') {
                        $repl['{CHECKED}'] = "{{$x[1]}-CHECKED}";
                        $this->multiCheckboxes[] = $x[1];
                    }
                    $inputFld = str_ireplace(array_keys($repl), array_values($repl), $this->HTML_Fields[strtolower($x[2])]);
                    $val = str_replace($x[0], $inputFld, $val);
                }
                #var_dump($val);
                $this->HTML_Input = $this->processMacros(
                    [
                        '{TABLE_DATA_CELLS}'=>$val,
                        '{CHECKBOX_ID}' => $this->HTML_Checkbox_Id,
                        '{CHECKBOX_VALUE}' => $this->HTML_Checkbox_Value,
                        '{HIDDEN_ID}' => $this->HTML_Hidden_Id,
                        '{HIDDEN_SEQ}' => str_replace('ID', 'SEQ', $this->HTML_Hidden_Id),
                    ],
                    $this->HTML_Input);
                #dbg('AFTER: HTML_Input='.htmlentities($this->HTML_Input));
                return true;
            case 'tableheadcells':
            case 'throw': // th_row
                #dbg('setting table_head_cells');
                if (is_array($val)) {
                    $val = '<th>'.implode('</th><th>', $val).'</th>';
                }
                if (preg_match('/{([^{}]*)\(checkallbox\)}/i', $val, $m)) {
                    $newHTML = str_replace('{LABEL_TEXT}', $m[1], $this->HTML_Checkallbox);
                    $val = str_replace($m[0], $newHTML, $val);
                    $this->HTML_Script[] = $this->HTML_Checkall_Script;
                    $this->HTML_Script[] = $this->HTML_Checkall_Init;
                }
                $this->HTML_Pre = $this->processMacros(
                    ['{TABLE_HEAD_CELLS}'=>$val], $this->HTML_Pre);
                #dbg("Setting Data Fields<br />\n");
                #dbg("_dataFields=".print_r($this->_dataFields,true));
                #dbg("_dataFieldLabels=".print_r($this->_dataFieldLabels,true));
                #dbg('AFTER: HTML_Pre='.htmlentities($this->HTML_Pre));
                return true;
            case 'searchable':
                if ($val) {
                    $this->MACRO_Table_Class .= ' table-list-search';
                } else {
                    dbg("Turning searchable OFF is not implemented");
                }
                return true;
            case 'label':
            case 'display':
            case 'checkboxlabel':
                $this->setMacro('Checkbox_Label', $val);
                return true;
        }
        return parent::handle1Opt($name, $val);
    }
    public function specialRowMacros(&$macros, $row) {
        // find cells with "{field(SELECT)}" and replace it with select/option tags
        // as specified in select(field) option
        foreach ($this->selectOptions as $k => $opts) {
            $html = "<select name=\"{$k}[$row[id]]\">";
            foreach ($opts as $val => $disp) {
                if (@$row[$k] == $val) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = "";
                }
                $html .= "<option value=\"$val\" $selected>$disp</option>";
            }
            $html .= '</select>';
            $macros['{'.$k.'(select)}'] = $html;
        }
        foreach ($this->multiCheckboxes as $field) {
            if (@$row[$field]) {
                $macros["{{$field}-CHECKED}"] = 'checked';
            } else {
                $macros["{{$field}-CHECKED}"] = '';
            }
        }
        #dbg("FormField_Table::specialRowMacros(): macros=");
        #pre_r($macros);
    }
} /* Table */

# Tab Table-of-Contents
abstract class US_FormField_TabToC extends FormField {
    protected $_fieldType = "tabtoc"; // tabbed table of contents
    protected $tocType = "tab"; // "pill" is an alternative
    protected $tocClass = "nav nav-tabs";
    public $repElement = 'HTML_Input';
    public
        $HTML_Pre = '
            <ul class="{TAB_UL_CLASS}" id="myTab"> <!-- ToC -->
            ',
        $HTML_Input = '
            <li class="{TAB_ACTIVE}"><a href="#{TAB_ID}" data-toggle="{TOC_TYPE}">{TITLE}</a></li>
             ',
        $HTML_Post = '
             </ul> <!-- ToC -->
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
                'tab_active'=>$o->getMacro('Tab_Pane_Active'),
                #'tab_active'=>$active,
                'toc_type'=>$toc_type,
            ];
            $active = '';
        }
        $this->repData = $tmp;
    }
} /* TabToC */

abstract class US_FormField_Text extends FormField {
    protected $_fieldType = "text";
} /* Text */

abstract class US_FormField_Textarea extends FormField {
    protected $_fieldType = "textarea";
    public
        $HTML_Input = '
              <textarea class="{INPUT_CLASS} {EDITABLE}" id="{FIELD_ID}" '
            .'name="{FIELD_NAME}" rows="{ROWS}" placeholder="{PLACEHOLDER}" '
            .'{REQUIRED_ATTRIB} {EXTRA_ATTRIB} {READONLY} {DISABLED}>{VALUE}</textarea>',
        $MACRO_Rows = '6',
        $MACRO_Editable = 'editable',
        $MACRO_Us_Url_Root = US_URL_ROOT,
        $MACRO_Tinymce_Url = null,
        $MACRO_Tinymce_Apikey = null,
        $MACRO_Tinymce_Plugins = null,
        $MACRO_Tinymce_Height = null,
        $MACRO_Tinymce_Menubar = null,
        $MACRO_Tinymce_Toolbar = null,
        $MACRO_Tinymce_Skin = null,
        $MACRO_Tinymce_Theme = null,
        $MACRO_Tinymce_Readonly = 'false',
        /* Note that TINYMCE_MENUBAR and TINYMCE_TOOLBAR have the quotes added separately */
        $HTML_Script = ['<script src="{TINYMCE_URL}"></script>',
            '<script>
                tinymce.init({
                    selector: \'#{FIELD_ID}\',
                    plugins: \'{TINYMCE_PLUGINS}\',
                    height: {TINYMCE_HEIGHT},
                    menubar: {TINYMCE_MENUBAR},
                    toolbar: {TINYMCE_TOOLBAR},
                    skin: \'{TINYMCE_SKIN}\',
                    theme: \'{TINYMCE_THEME}\',
                    statusbar: false,
                    elementpath: false,
                    readonly: {TINYMCE_READONLY},
                 });
            </script>'];
    public function handleOpts($opts) {
        $rtn = parent::handleOpts($opts);
        $tinymceOpts = [
            'Tinymce_Url'=>'tinymce_url',
            'Tinymce_Apikey'=>'tinymce_apikey',
            'Tinymce_Plugins'=>'tinymce_plugins',
            'Tinymce_Menubar'=>'tinymce_menubar',
            'Tinymce_Toolbar'=>'tinymce_toolbar',
            'Tinymce_Height'=>'tinymce_height',
            'Tinymce_Skin'=>'tinymce_skin',
            'Tinymce_Theme'=>'tinymce_theme',
        ];
        foreach ($tinymceOpts as $macro => $config) {
            if (!$this->getMacro($macro) && ($x = configGet($config))) {
                $this->setMacro($macro, $x);
            }
        }
        if ($this->getMacro('Tinymce_Apikey') && ($tinyUrl = $this->getMacro('Tinymce_Url'))) {
            if (stripos($tinyUrl, '{TINYMCE_APIKEY}') === false) {
                $this->setMacro('Tinymce_Apikey', $tinyUrl.'?apiKey={TINYMCE_APIKEY}');
            }
        }
        /* These options can be true or false or a string - thus have to explicitly add quotes */
        foreach (['Tinymce_Menubar', 'Tinymce_Toolbar'] as $macro) {
            if (!in_array($x = $this->getMacro($macro), ['true', 'false']) && $x{0} != "'") {
                $this->setMacro($macro, "'".$x."'");
                #dbg($this->getMacro($macro));
            }
        }
        /* Technically this should be substituted automatically. Due to the order of the substitutions
         * (since this macro is contained within another) sometimes it doesn't work. Rather than making
         * a (complicated) system for ordering the macros, we just do a quick substitute here...
         */
        if (($tinyUrl = $this->getMacro('Tinymce_Url')) && stripos($tinyUrl, '{TINYMCE_APIKEY}') === false) {
            $this->setMacro('Tinymce_Url', str_ireplace('{US_URL_ROOT}', $this->MACRO_Us_Url_Root, $tinyUrl));
        }
        if ($this->getMacro('Readonly') || $this->getMacro('Disabled')) {
            $this->setMacro('Tinymce_Readonly', 'true');
            $this->setMacro('Tinymce_Toolbar', 'false');
            $this->setMacro('Tinymce_Menubar', 'false');
        }

        return $rtn;
    }
} /* Textarea */
