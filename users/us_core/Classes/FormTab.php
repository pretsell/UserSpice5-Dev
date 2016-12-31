<?php
class US_FormTab_Contents extends Form {
    public $elementList = [
        'openTab', 'Fields', 'closeTab',
    ];
    protected $HTML_openTab = '
        <div class="tab-content {TAB_CONTENT_CLASS}" id="{TAB_ID}">
        ';
    protected $HTML_closeTab = '
        </div> <!-- tab-content -->
        ';
}
class US_FormTab_Pane extends Form {
    public $elementList = [
        'openTab', 'Fields', 'closeTab',
    ];
    protected $HTML_openTab = '
        <div class="tab-pane {TAB_PANE_ACTIVE} {TAB_PANE_CLASS}" id="{TAB_ID}">
        ';
    protected $HTML_closeTab = '
        </div> <!-- tab-pane -->
        ';
}
class US_Form_Form extends Form {
    public $elementList = [
        'openForm', 'Fields', 'closeForm',
    ];
}
class US_Form_Col extends Form {
    public $elementList = [
        'openCol', 'Fields', 'closeCol',
    ];
}
class US_Form_Row extends Form {
    public $elementList = [
        'openRow', 'Fields', 'closeRow',
    ];
}
class US_Form_Well extends Form {
    public $elementList = [
        // since we are changing the order, maybe just re-use title?
        'openWell', 'Title', 'Fields', 'closeWell',
    ];
    protected $HTML_Title = '
        <h4>{FORM_TITLE}</h4>
        ';
    protected $HTML_openWell = '
        <div class="well {WELL_CLASS}">';
    protected $HTML_closeWell = '
        </div> <!-- well -->
        ';
    public $MACRO_Well_Class = 'well-sm';
}
