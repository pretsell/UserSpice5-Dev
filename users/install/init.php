<?php
/*
 * install/init.php
 * This script is included to give the basic redirect and path capabilities
 * within installation scripts.
 */

error_reporting(E_ALL);
ini_set("display_errors",1);
if (!defined("US_ROOT_DIR")) {
    // z_us_root.php is located in .. which can be accessed by dirname(dirname(__FILE__))
    require_once(dirname(dirname(__FILE__))."/z_us_root.php");
}
require_once(US_ROOT_DIR."/us_core/Classes/Redirect.php");
require_once(US_ROOT_DIR."/local/Classes/Redirect.php");
require_once(US_ROOT_DIR."/us_core/Classes/Config.php");
require_once(US_ROOT_DIR."/local/Classes/Config.php");
if (file_exists(US_ROOT_DIR."local/config.php")) {
    require_once(US_ROOT_DIR."local/config.php");
    # If we have $cfg from local/config.php then we (should?) have DB access
    require_once(US_ROOT_DIR."/us_core/Classes/DB.php");
    require_once(US_ROOT_DIR."/local/Classes/DB.php");
}
# Note that not all functions in us_helpers.php will work - dependencies
# But some will...
require_once(US_ROOT_DIR."/us_core/helpers/us_helpers.php");
