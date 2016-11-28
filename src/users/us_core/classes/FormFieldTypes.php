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


# Unfortunately FormField_Button cannot be customized without more work
# (to do that we would have to have a special script just for this one
# in order to get the order of the script-loading to work - a 3rd level)
abstract class FormField_Button extends FormField {
    protected $_fieldType = "submit";
    protected $_isDBField = false; // more appropriate default for most buttons
    protected $_fieldValue = "pressed";
    public $HTMLPre = '',
        $HTMLInput =
            '<button class="{INPUT-CLASS}" name="{FIELD-NAME}" value="{VALUE}" /><span class="{BUTTON-ICON}"></span> {LABEL-TEXT}</button>
            ',
        $HTMLPost = '';
    public function fixSnippets() {
        parent::fixSnippets();
        $this->_macros['{INPUT-CLASS}'] = 'btn btn-primary';
        $this->_macros['{BUTTON-ICON}'] = '';
    }
}
abstract class US_FormField_ButtonAnchor extends FormField_Button {
    protected $_fieldType = "button";
    public $HTMLPre = '',
        $HTMLInput =
            '<a href="{HREF}" class="{INPUT-CLASS}" type="{TYPE}"><span class="{BUTTON-ICON}"></span> {LABEL-TEXT}</a>
            ',
        $HTMLPost = '';
}
abstract class US_FormField_ButtonSubmit extends FormField_Button {
}
abstract class US_FormField_ButtonDelete extends FormField_Button {
    public function fixSnippets() {
        parent::fixSnippets();
        $this->_macros['{INPUT-CLASS}'] = 'btn btn-primary btn-danger';
    }
}

abstract class US_FormField_Checkbox extends FormField {
    protected $_fieldType = "checkbox";
	public $HTMLPre =
            '<div class="{DIV-CLASS}">
            ',
        $HTMLInput =
    		'<input type="{TYPE}" name="{FIELD-NAME}" id="{FIELD-ID}" >
            ',
        $HTMLPost =
		    '<label class="{LABEL-CLASS}" for="{FIELD-ID}">{LABEL-TEXT}</label>
        	 </div>
             ';
}

abstract class US_FormField_Hidden extends FormField {
    protected $_fieldType = "hidden";
    # Leave just the standard <input ...>
    public $HTMLPre = '',
        $HTMLInput =
            '<input type="{TYPE}" name="{FIELD-NAME}" value="{VALUE}">
            ',
        $HTMLPost = '';
}

abstract class US_FormField_Password extends FormField {
    protected $_fieldType = "password";
}

abstract class US_FormField_Radio extends FormField {
    protected $_fieldType = "radio";
    public
        $HTMLPre =
            '<div class="{DIV-CLASS}">
             <label class="{LABEL-CLASS}" for="{FIELD-ID}">{LABEL-TEXT}
             <span class="{HINT-CLASS}" title="{HINT-TEXT}"></span></label>
             ',
        $HTMLInput =
            '<div class="radio">
				<label for="{FIELD-ID}-{ID}">
					<input type="{TYPE}" name="{FIELD-NAME}" id="{FIELD-ID}-{ID}" class="{INPUT-CLASS}" value="{ID}">
					{OPTION-LABEL}
				</label>
			</div> <!-- radio -->
            ',
        $HTMLPost =
            '</div>
            ';
}

abstract class US_FormField_Recaptcha extends FormField {
    protected $_fieldType = "recaptcha"; // not used
    protected $_validateErrors = [];
    public $HTMLPre =
            '<div class="{DIV-CLASS}">
    		 <label>{LABEL-TEXT}</label>
             ',
        $HTMLInput =
            '<div class="{RECAPTCHA-CLASS}" name="{RECAPTCHA-PUBLIC}"></div>
            ',
        $HTMLPost =
            '</div>
            ',
        $HTMLScript = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
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
    public function fixSnippets() {
        parent::fixSnippets();
        $this->_macros['{RECAPTCHA-CLASS}'] = 'g-recaptcha';
        $this->_macros['{RECAPTCHA-PUBLIC}'] = configGet('recaptcha_public');
    }
}

abstract class US_FormField_Select extends FormField {
    protected $_fieldType = "select";
    public
        $HTMLPre =
            '<div class="{DIV-CLASS}">
             <label class="{LABEL-CLASS}" for="{FIELD-ID}">{LABEL-TEXT}
             <span class="{HINT-CLASS}" title="{HINT-TEXT}"></span></label>
             <br />
             <select class="{INPUT-CLASS}" id="{FIELD-ID}" name="{FIELD-NAME}">
             ',
        $HTMLInput =
            '<option value="{ID}">{OPTION-LABEL}</option>
             ',
        $HTMLPost =
            '</select>
             </div>
             ';
}
abstract class US_FormField_Table extends FormField {
    protected $_fieldType = "table";
    public
        $HTMLPre =
            '<div class="{DIV-CLASS}">
             <table class="{TABLE-CLASS}">
             <tr class="{TH-ROW-CLASS}">{TABLE-HEAD-CELLS}</tr>
             ',
        $HTMLInput =
            '<tr class="{TD-ROW-CLASS}">{TABLE-DATA-CELLS}</tr>
             ',
        $HTMLPost =
            '</table>
             </div>
             ';
}

abstract class US_FormField_TabToC extends FormField {
    protected $_fieldType = "tabtoc"; // tabbed table of contents
    protected $tocType = "tab";
    protected $tocClass = "nav nav-tabs";
    public
        $HTMLPre = '
            <ul class="{TAB-UL-CLASS}" id="myTab">
            ',
        $HTMLInput = '
            <li class="{TAB-ACTIVE}"><a href="#{TAB-ID}" data-toggle="{TOC-TYPE}">{TITLE}</a></li>
             ',
        $HTMLPost = '
             </ul>
             ';
    public function __construct($a, $opts) {
        foreach ($opts as $k=>$v) {
            switch (strtolower($k)) {
            case 'toc-type':
            case 'toc_type':
                $this->tocType = $v;
                $this->tocClass = 'nav nav-'.$v.'s'; # nav-tabs or nav-pills usually
                break;
            }
        }
        return parent::__construct($a, $opts);
    }
    public function getHTML($opts=[]) {
        #dbg("getHTML: _macros=<pre>".print_r($this->_macros,true)."</pre><br />");
        #dbg("tocType=".$this->tocType);
        $this->_macros['{TAB-UL-CLASS}'] = $this->tocClass;
        return parent::getHTML($opts);
    }
    public function setRepeatValues($opts=[]) {
        // typically getting an array from Form::getFields()
        $tmp = [];
        $active = 'active'; // first one active
        $toc_type = (isset($opts['toc-type']) ? : $this->tocType);
        foreach ($opts as $k=>$o) {
            #dbg('Class Name: '.get_class($o));
            $tmp[] = [
                'title'=>$o->getTitle(),
                'tab-id'=>$k,
                'tab-active'=>$active,
                'toc-type'=>$toc_type,
            ];
            $active = '';
        }
        $this->_repeatValues = $tmp;
    }
}

abstract class US_FormField_Text extends FormField {
    protected $_fieldType = "text";
}
