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

class Config {
	private $_site_settings = null;
	private function __construct() {
		$this->getSiteSettings();
	}
	private function getSiteSettings() {
		$db = new DB();
		$_site_settings = $db->query("SELECT * FROM settings")->first();
	}
	public function get($path=null, $default=null) {
		if (is_null($this->_site_settings)) {
			$this->getSiteSettings();
		}
		// Settings can be stored in the settings table - if so, this takes priority
		if (isset($this->_$cfg->get('')$path)) {
			return $this->_$cfg->get('')$path;
		}
		// Not in settings table? Look in $GLOBALS['config'] array
		if ($path) {
			$config = $GLOBALS['config'];
			$path = explode('/', $path);

			foreach ($path as $bit) {
				if (isset($config[$bit])) {
					$config = $config[$bit];
				} else {
					return false;
				}
			}
			return $config;
		}
		// $path not found - return $default (null if not passed in)
		return $default;
	}
}
