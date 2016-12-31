<?php
/*
 * FormFieldRepeat.php
 *
 * Some field values have a repeating element (such as <option>s
 * in <select>...</select> or the rows of a <table> or etc.). They
 * have to be handled specially and this class implements that.
 * $this->HTMLInput contains the element to be repeated while
 * $this->HTMLPre and $this->HTMLPost get prepended/appended as
 * appropriate.
 */
abstract class US_FormFieldRepeat extends FormField {
    protected
        $_idField='id', // used to determine which repeat row is match
        $_repeatAliases = ['{OPTION_VALUE}', '{OPTION_LABEL}'], // this will provide an alias
                            // replacement text for the first n values in each element of $_repeatValues
                            // thus '{OPTION_LABEL}' will be replaced with 'label1' and then 'label2'
                            // using the example data below
        $_repeatValues = []; // e.g., [['id'=>1, 'name'=>'label1'], ['id'=>2, 'name'=>'label2'], ...]
                            // this will provide replace macros for {ID} and {NAME}, respectively
    public $HTMLEmptyAlternate='<p>(No Data)</p>';

    public function xhandle1Opt($name, $val) {
        return parent::handle1Opt($name, $val);
        switch(strtolower($name)) {
            case 'repdata':
                $this->setRepData($val);
                break;
            case 'nodata':
            case 'no_data':
            case 'alternate_empty':
            case 'empty_alternate':
                $this->setEmptyAlternate($val);
                break;
            case 'idfield':
                $this->setIdField($val);
                break;
        }
    }

    # $opts is a hash which can have the following indexed values:
    #  'replaces' => ['{search}'=>'replace',...]
    public function getHTMLElements($opts) {
        # Calculate $this->HTMLInput. Since this is a repeating field
        # (<select...> with <option...>,..., for instance) then each row
        # in $this->_repeatValues gets its own copy of $this->HTMLInput
        # with the values appropriately substituted
        $this->inter_br = '';
        $html = '';
        if ($this->getRepeatValues()) {
            foreach ($this->getRepeatValues() as $repeatVal) {
                $rowMacros = [];
                # Set any special just-in-time replacement macros for specific field types
                $this->jitMacrosPerRow($repeatVal, $rowMacros);
                $aliasIdx = 0;
                foreach ($repeatVal as $fld=>$val) {
                    $rowMacros['{'.strtoupper($fld).'}'] = $val;
                    if (isset($this->_repeatAliases[$aliasIdx])) {
                        $rowMacros[$this->_repeatAliases[$aliasIdx]] = $val;
                    }
                    $aliasIdx++;
                }
                #dbg("Replacing for row: ".htmlentities($this->HTMLInput));
                #var_dump($rowMacros);
                $html .= str_replace(array_keys($rowMacros), array_values($rowMacros), $this->HTMLInput)."\n";
                $this->inter_br = "<br />\n";
            }
            $html = $this->HTMLPre . $html . $this->HTMLPost;
        } else {
            $html = $this->getEmptyAlternate();
        }
        return $html;
    }

    // these are per-repeating-row just-in-time replacement macros
    public function jitMacrosPerRow($row, &$macros) {
        $macros['{INTER_BR}'] = $this->inter_br; // {INTER_BR} must be at *start* of element
    }

    public function setRepeatValues($opts) {
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
    public function getRepeatValues() {
        return $this->_repeatValues;
    }
    public function addRepeatValue($opts=array()) {
        $this->_repeatValues[] = $opts;
    }
    public function getIdField() {
        return $this->_idField;
    }
    public function setIdField($val) {
        $this->_idField = $val;
    }
    public function setEmptyAlternate($val) {
        $this->HTMLEmptyAlternate = $val;
    }
    public function getEmptyAlternate() {
        return $this->HTMLEmptyAlternate;
    }
}
