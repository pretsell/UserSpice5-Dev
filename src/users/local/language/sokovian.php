<?php
/*
 * This script demonstrates how individual language tokens or the entire language
 * set can be over-ridden here in local/language/<mylang>.php.
 */
$lang = array_merge($lang, array(
    'GROUP_TYPE_NAME_LABEL' => 'some crazy label override in local/language/sokovian.php',
));
