<?php
class US_FormTab_Contents extends Form {
    public $elementList = [
        'openTab', 'Fields', 'closeTab',
    ];
    protected $HTML_openTab = '
        <div class="tab-content {TAB_CONTENT_CLASS}" id="{TAB_ID}">
        ';
    protected $HTML_closeTab = '
        </div> <!-- tab-content (id={TAB_ID}) -->
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
        </div> <!-- tab-pane (id={TAB_ID}) -->
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
class US_Form_Panel extends Form {
    public $elementList = [
        // since we are changing the order, maybe just re-use title?
        'openPanel', 'Heading', 'openBody', 'Title', 'Fields', 'closeBody', 'Footing', 'closePanel',
    ];
    protected $HTML_Head = '',
        $HTML_Foot = '';
    protected $HTML_Title = '
        <h4>{FORM_TITLE}</h4>
        ';
    protected $HTML_openPanel = '
        <div class="panel {PANEL_COLOR} {PANEL_CLASS}">';
    protected $HTML_openBody = '
        <div class="panel-body {BODY_CLASS}">';
    protected $HTML_closeBody = '
        </div> <!-- panel-body (title={FORM_TITLE}) -->
        ';
    protected $HTML_closePanel = '
        </div> <!-- panel -->
        ';
    public $MACRO_Panel_Class = '',
        $MACRO_Panel_Color = 'panel-default', // panel-primary, -success, -info, -warning, or -danger
        $MACRO_Body_Class = '',
        $MACRO_Head_Class = '',
        $MACRO_Foot_Class = '',
        $MACRO_Form_Title = ''; // if null it reads title from pages table which is not usually right for a panel
    public function getHTMLHeading() {
        if ($this->HTML_Head) {
            return '<div class="panel-heading {HEAD_CLASS}">'.$this->HTML_Head.'</div>';
        } else {
            return '';
        }
    }
    public function getHTMLFooting() {
        if ($this->HTML_Foot) {
            return '<div class="panel-footer {FOOT_CLASS}">'.$this->HTML_Foot.'</div>';
        } else {
            return '';
        }
    }
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
        </div> <!-- well (title={FORM_TITLE}) -->
        ';
    public $MACRO_Well_Class = 'well-sm';
}
