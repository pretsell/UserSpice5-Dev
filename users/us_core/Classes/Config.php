<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class US_Config {
	private $_site_settings = null;
    private $_config_hash = array();

	public function __construct($cfg_hash) {
        #echo "DEBUG: US_Config::___construct(): Entering<br />\n";
        #var_dump($cfg_hash);
        if ($cfg_hash) {
            #echo "DEBUG: Found cfg_hash<br />\n";
            $this->_config_hash = $cfg_hash;
        }
		$this->setSiteSettings();
        #var_dump($this->_site_settings);
	}
	private function setSiteSettings() {
        # Note that $T is not yet defined in init.php when this script
        # is included (and cannot be - we end up with circular requirements)
        # Thus we manually sort out the $prefix below rather than using $T.
        $host   = $this->simpleGet('mysql/host');
        $dbName = $this->simpleGet('mysql/db');
        $user   = $this->simpleGet('mysql/username');
        $passwd = $this->simpleGet('mysql/password');
        $opts   = $this->simpleGet('mysql/opts');
        $prefix = $this->simpleGet('mysql/prefix');
        # we cannot use normal DB class because that class relies on this class
        # to get dbHost, dbName, etc. So, instead, we use PDO() directly.
		$dbConx = new PDO('mysql:host='.$host.';dbname='.$dbName, $user, $passwd, $opts);
		$this->_site_settings = $dbConx->query("SELECT * FROM {$prefix}settings")->fetch(PDO::FETCH_OBJ);
	}
	public function get($path=null, $default=null) {
		#echo "DEBUG: Config::get($path, $default): Entering<br />\n";
		if (!isset($this->_site_settings)) {
			$this->setSiteSettings();
		}
		// Settings can be stored in the settings table - if so, this takes priority
		if (isset($this->_site_settings->$path)) {
			return $this->_site_settings->$path;
		}
		// Not in settings table? Look in $GLOBALS['config'] array
		return $this->simpleGet($path, $default);
	}
	// Particularly during initialization (i.e., before the DB is ready for use)
	// we have to look up some config variables. Thus this separation to allow looking
	// up config values *only* from the array-based system(s).
	public function simpleGet($path=null, $default=null) {
		#echo "DEBUG: Config::simpleGet($path, $default): Entering<br />\n";
		if ($path) {
			$config = $this->_config_hash;
			$path = explode('/', $path);

			foreach ($path as $bit) {
				if (isset($config[$bit])) {
					$config = $config[$bit];
				} else {
					return $default;
				}
			}
			return $config;
		}
		// $path not found - return $default (null if not passed in)
		return $default;
	}
}

if (!function_exists('configGet')) {
	//Get value using Config::get()
    // this is just a shortcut to avoid having "global $cfg" at the top of
    // every function. It assumes that $cfg has already been set.
	function configGet($path, $default=null) {
		global $cfg;
        return $cfg->get($path, $default);
	}
}
