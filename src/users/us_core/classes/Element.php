<?php
/*
 * US_Element (becomes Element in local/Classes) is the base class
 * for all Form and FormField classes.
 *
 * It is designed to allow HTML elements (including nested elements)
 * to be generated dynamically.
 *
 * Properties:
 * $elementList
 *   This array contains the list of elements which will be used to generate
 *   the full HTML returned. Strings in this array will be prefixed with 'HTML_'
 *   and then accessed, so see "$HTML_*" below.
 *   Examples:
 *     For a form:
 *       ['Header', 'OpenContainer', 'OpenRow', 'OpenCol', 'OpenForm', ...]
 *       (see Form.php for an actual example)
 *     For a select field:
 *       ['openSelect', 'options', 'closeSelect']
 *       (see FormField_Select.php for an actual example)
 * $HTML_*
 *   Properties named with this prefix will be used as HTML templates
 *   (strings with macros which get replaced with actual values in order
 *   to generate HTML)
 * $MACRO_*
 *   Properties named with this prefix will be used as macro replacement
 *   values. For instance $MACRO_foo='bar' means that every occurrence
 *   of '{FOO}' in a $HTML_* template will be replaced with 'bar'
 *   Note that text to be replaced ({FOO}) is case insensitive but
 *   uppercase by convention.
 *
 * THESE PROPERTIES ARE FOR USE WITH REPEATING ELEMENTS:
 * $repElement
 *   When a given element must repeat n times (n=number of rows in
 *   $repData [described below]), this property is set to the name
 *   of that element (INCLUDING the HTML_ prefix if appropriate!)
 *   Example:
 *     For a form:
 *       'fields' - $x->getHMTL() will be called on each row in $repData
 *     For a select field:
 *       'options' - $HTML_Options will be repeated for each row in $repData
 * $repData
 *   When a given element must repeat n times, the data to create
 *   that repetition is stored in this property. It can be initialized as an
 *   array of either objects ($row[0]->id) or of assoc array ($row[0]['id'])
 *   but will be converted to an assoc array internally.
 * $repMacroAliases
 *   Sometimes it is convenient to have aliases based on the position within
 *   the record of $repData. For instance, {OPTION-VALUE} and {OPTION-LABEL}
 *   rather than having to adjust the macro for {ID} and {NAME} one time and
 *   {ID} and {SHORT_NAME} the next time. The values of this array will become
 *   aliases for the first n fields within the records of $repData.
 * $HTML_RepEmptyAlternate
 *   If a repeating element has no data, this value will be used as the
 *   alternate.
 */
abstract class US_Element {
    protected $_db=null;
    public $elementList = [];
    public $repElement = null;
    public $repData = [];
    public $repMacroAliases = [];
    public $HTML_RepEmptyAlternate = '';
    # public $HTML_x = '<input type="{TYPE}" name="{NAME}"' ... />';
    # public $MACRO_type = 'hidden';
    public function __construct($opts=[]) {
        $this->_db = DB::getInstance();
        if (isset($opts['elements'])) {
            $this->setElementList($opts['elements']);
        }
        if ($reps = $this->getRepDataOpt($opts)) {
            $this->setRepData($reps);
        }
        $this->handleOpts($opts);
    }
    public function handleOpts($opts) {
        foreach ($opts as $k=>$v) {
            $this->handle1Opt($k, $v);
        }
    }
    // when this method is overridden you probably want to call
    // parent::handle1Opt() to get the benefit of parent option handling
    public function handle1Opt($name, $val) {
        if (strtolower($name) == 'repemptyalternate') {
            $this->setRepEmptyAlternate($val);
            return true;
        }
        $setMethod = 'set'.$name;
        $propHTML = 'HTML_'.$name;
        $propMacro = 'MACRO_'.$name;
        #dbg("name=$name");
        if (method_exists($this, $setMethod)) {
            #dbg("METHOD: $setMethod");
            $this->$setMethod($val);
            return true;
        } elseif (property_exists($this, $propHTML)) {
            #dbg("PROP HTML: $propHTML");
            $this->$propHTML = $val;
            return true;
        } elseif (property_exists($this, $propMacro)) {
            #dbg("PROP MACRO: $propMacro, val=$val");
            $this->$propMacro = $val;
            return true;
        }
        return false;
    }
    protected function getRepDataOpt($opts) {
        $repDataNames = ['repData', 'repeats'];
        if ($this->repElement) {
            $repDataNames[] = $this->repElement;
            if (substr($this->repElement, 0, strlen('HTML_')) == 'HTML_') {
                $repDataNames[] = substr($this->repElement, strlen('HTML_'));
            }
        }
        foreach ($repDataNames as $i) {
            if (isset($opts[$i])) {
                return $opts[$i];
            }
        }
    }
    public function getHTML($opts=[]) {
        $elementList = $this->getElementList($opts);
        $html = '';
        foreach ((array)$elementList as $e) {
            #dbg(substr($this->getHTMLElement($e, $opts), 0, 30));
            $html .= $this->getHTMLElement($e, $opts);
            #echo "\n\n===$e (".get_class($this).")===\n\n$html\n\n";
        }
        return $this->processMacros($html, $opts);
    }
    public function getElementList($opts=[]) {
        if (isset($opts['elements'])) {
            return $opts['elements'];
        } elseif (isset($opts['exclude_elements'])) {
            return array_diff($this->elementList, $opts['exclude_elements']);
        } else {
            return $this->elementList;
        }
    }
    public function setElementList($elementList) {
        $this->elementList = $elementList;
    }
    public function getHTMLElement($element, $opts) {
        #dbg("getHTMLElement($element, \$opts)");
        if (is_string($element)) {
            $methodName = 'getHTML'.$element;
            $propName = 'HTML_'.$element;
            $prop2Name = $element;
            if (method_exists($this, $methodName)) {
                #dbg("getHTMLElement(): Method");
                $html = $this->$methodName($opts);
            } elseif (in_array($this->repElement, [$propName, $prop2Name])) {
                # repeating element
                $propName = $this->repElement;
                $elem = isset($this->$propName) ? $this->$propName : $propName;
                $html = $this->getHTMLRepElement($elem, $opts);
            } elseif (isset($this->$propName) || isset($this->$prop2Name)) {
                #dbg("getHTMLElement(): Prop");
                if (!isset($this->$propName)) {
                    $propName = $prop2Name; // esp 'Fields'
                }
                $html = $this->$propName;
            } else {
                #dbg("getHTMLElement(): String");
                $html = $element;
            }
        } elseif (method_exists($element, 'getHTML')) {
            $html = $element->getHTML($opts);
        }
        #dbg("getHTMLElement(<pre>".substr($html, 0, 30)."</pre>...): Entering (".get_class($this).")");
        return $html;
    }
    public function getHTMLRepElement($element, $opts) {
        #dbg("getHTMLRepElement(<pre>".substr($element, 0, 20)."</pre>...): Entering (".get_class($this).")");
        if ($this->repDataIsEmpty()) {
            return $this->getRepEmptyAlternate();
        }
        #dbg("getHTMLRepElement(): Not empty");
        $html = '';
        foreach ($this->getRepData() as $k=>$row) {
            #dbg(get_class($this).'==>'.$k);
            #var_dump($row);
            if (is_object($row)) {
                #dbg("OBJECT==>".get_class($row));
                if (method_exists($row, 'getHTML')) {
                    $element = $row->getHTML();
                }
                if (method_exists($row, 'getRowMacros')) {
                    $rowMacros = $row->getRowMacros();
                } else {
                    $rowMacros = [];
                }
            } elseif (is_string($row)) {
                #dbg("SIMPLE STRING");
                $element = $row;
                $rowMacros = [];
            } else {
                #dbg("PRESUMABLY ASSOCIATIVE ARRAY");
                $rowMacros = [];
                $idx = 0;
                foreach ($row as $k=>$v) {
                    $rowMacros['{'.$k.'}'] = $v;
                    if (isset($this->repMacroAliases[$idx])) {
                        $rowMacros[$this->repMacroAliases[$idx]] = $v;
                    }
                    $idx++;
                }
                // additional macros may be added to $rowMacros here
                $this->specialRowMacros($rowMacros, $row);
            }
            #var_dump($rowMacros);
            $html .= str_ireplace(array_keys($rowMacros), array_values($rowMacros), $element);
        }
        return $html;
    }
    public function getRowMacros() {
        return [];
    }
    public function specialRowMacros(&$macros, $row) {
        // in children classes this may set 'selected="selected"' or
        // 'checked="checked"' or etc.
    }
    public function repDataIsEmpty() {
        return !(boolean)$this->repData;
    }
    public function processMacros($html, $opts) {
        if (in_array('nomacros', $opts)) {
            return $html;
        }
        $macros = $this->getMacros($html, $opts);
        #var_dump($macros);
        return str_ireplace(array_keys($macros), array_values($macros), $html);
    }
    public function getMacros($s, $opts) {
        foreach ($this->_getPropsByPrefix("MACRO_") as $k=>$v) {
            $macros['{'.$k.'}'] = $v;
        }
        return $macros;
    }
    public function getMacro($name) {
        $prop = 'MACRO_'.$name;
        return $this->$prop;
    }
    public function setMacro($name, $val) {
        $prop = 'MACRO_'.$name;
        if (property_exists($this, $prop)) {
            $this->$prop = $val;
            return true;
        } else {
            return false;
        }
    }
    public function getHTMLElements($opts) {
        return $this->_getPropsByPrefix("HTML_");
    }
    public function getRepEmptyAlternate() {
        return $this->HTML_RepEmptyAlternate;
    }
    public function setRepEmptyAlternate($val) {
        $this->HTML_RepEmptyAlternate = $val;
    }
    public function setRepData($val) {
        $this->repData = $val;
    }
    public function getRepData() {
        return $this->repData;
    }
    private function _getPropsByPrefix($prefix) {
        $props = get_object_vars($this);
        #var_dump($props);
        $props = array_filter(get_object_vars($this), function ($v, $k) use ($prefix) {
            if (strncmp($prefix, $k, strlen($prefix)) == 0) {
                return true;
            }
            return false;
        }, ARRAY_FILTER_USE_BOTH);
        #var_dump($props);
        $props = array_combine(
            str_replace($prefix, '', array_keys($props)),
            array_values($props));
        #var_dump($props);
        return $props;
    }
}
