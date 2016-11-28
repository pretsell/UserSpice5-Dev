<?php
class US_FormTab_Contents extends US_FormTab_Pane {
    /*
    public
        $useCSRF=false,
        $useHeader=false,
        $useNavigation=false,
        $useContainer=false,
        $useTab=true,
        $useAdminDashboard=false,
        $useTitle=false,
        $useTitleAndResults=false,
        $useForm=false,
        $useRowCol=false    ,
        $usePageFooter=false,
        $useHTMLFooter=false;
    */
    # Use the same "TabBlock" for content as for pane with just
    # slightly different HTML
    protected $_openTabBlock = '
        <div class="tab-content {TAB-CONTENT-CLASS}" id="{TAB-ID}">
        ';
    protected $_closeTabBlock = '
        </div> <!-- tab-content -->
        ';
}
class US_FormTab_Pane extends Form {
    public
        $useCSRF=false,
        $useHeader=false,
        $useNavigation=false,
        $useContainer=false,
        $useTab=true,
        $useAdminDashboard=false,
        $useTitle=false,
        $useTitleAndResults=false,
        $useForm=false,
        $useRowCol=false,
        $usePageFooter=false,
        $useHTMLFooter=false;
}
