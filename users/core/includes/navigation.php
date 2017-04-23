<?php
if (file_exists(US_ROOT_DIR.'local/images/logo.png')) {
    $logo = US_URL_ROOT."/local/images/logo.png";
} else {
    $logo = US_URL_ROOT."/core/images/logo.png";
}
echo getMenu('main', $logo);
