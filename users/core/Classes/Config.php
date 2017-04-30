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
	public function setSiteSettings() {
        # Note that $T is not yet defined in init.php when this script
        # is included (and cannot be - we end up with circular requirements)
        # Thus we manually sort out the $prefix below rather than using $T.
        $mysql_host   = $this->simpleGet('mysql/host');
        $mysql_dbName = $this->simpleGet('mysql/db');
        $mysql_user   = $this->simpleGet('mysql/username');
        $mysql_passwd = $this->simpleGet('mysql/password');
        $mysql_opts   = $this->simpleGet('mysql/opts');
        $mysql_prefix = $this->simpleGet('mysql/prefix');
        # we cannot use normal DB class because that class relies on this class
        # to get dbHost, dbName, etc. So, instead, we use PDO() directly.
        if (isset($T['settings'])) {
            $settings = $T['settings'];
        } else {
            $settings = $mysql_prefix.'settings';
        }
		$dbConx = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_dbName, $mysql_user, $mysql_passwd, $mysql_opts);
		if ($stmt = $dbConx->query("SELECT * FROM {$settings} WHERE (user_id IS NULL OR user_id <= 0) AND (group_id IS NULL OR group_id <= 0)")) {
    		$this->_site_settings = $stmt->fetch(PDO::FETCH_OBJ);
        } else {
            $this->_site_settings = new stdClass;
        }
    }
    public function overrideSiteSettings() {
        global $user, $T;
        $db = DB::getInstance();
        if (isset($user) && $userId = $user->id()) {
            $overridable = [
                'site_language' => 'override_site_language',
                'debug_mode' => 'override_debug_mode',
                'enable_messages' => 'override_enable_messages',
                'multi_row_after_delete' => 'override_after_actions',
                'multi_row_after_create' => 'override_after_actions',
                'multi_row_after_edit' => 'override_after_actions',
                'single_row_after_delete' => 'override_after_actions',
                'single_row_after_create' => 'override_after_actions',
                'single_row_after_edit' => 'override_after_actions',
                'tinymce_plugins' => 'override_tinymce',
                'tinymce_height' => 'override_tinymce',
                'tinymce_menubar' => 'override_tinymce',
                'tinymce_skin' => 'override_tinymce',
                'tinymce_theme' => 'override_tinymce',
                'tinymce_toolbar' => 'override_tinymce',
                'date_fmt' => 'override_date_fmt',
                'time_fmt' => 'override_time_fmt',
            ];
            $sql = "SELECT *, 10 AS priority
                    FROM $T[settings] settings
                    WHERE (user_id = ?)
                    UNION
                    SELECT settings.*, 0 AS priority
                    FROM $T[settings] settings
                    JOIN $T[groups_users] ug ON (settings.group_id = ug.group_id)
                    WHERE ug.user_id = ?
                    ORDER BY priority, group_id";
    		if ($db->query($sql, [$userId, $userId])->count()) {
        		foreach ($db->results() as $setting) {
                    foreach ($overridable as $k=>$v) {
                        if ($setting->$v) {
                            $this->_site_settings->$k = $setting->$k;
                        }
                    }
                }
            }
        }
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
