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
class US_Input {
	public static function exists($type = 'post'){
		switch ($type) {
			case 'post':
				return (!empty($_POST)) ? true : false;
				break;

			case 'get':
				return (!empty($_GET)) ? true : false;

			default:
				return false;
				break;
		}
	}

	public static function get($item, $method='request') {
        #dbg("Input::get(): Entering");
		switch (strtolower($method)) {
			case 'get':
				$src = &$_GET;
				break;
			case 'post':
				$src = &$_POST;
				break;
			default:
				$src = &$_REQUEST;
				break;
		}
		if (isset($src[$item])) {
            return self::_getItem($src[$item]);
            /*
			# If the item is an array, process each item independently, and return array of sanitized items.
			if (is_array($src[$item])){
				$items=array();
				foreach ($src[$item] as $k => $item){
					$items[$k]=self::sanitize($item);
				}
				return $items;
			} else {
				return self::sanitize($src[$item]);
			}
            */
		}
		return '';
	}
    // recursive function to process recursive arrays in $_POST/$_GET/$_REQUEST
    private static function _getItem($items) {
        #dbg("_getItem(): Entering");
		if (is_array($items)){
            #dbg("array");
			$newItems=[];
			foreach ($items as $k => $item){
                #dbg("k=$k, item=<pre>".print_r($item,true)."</pre>");
				$newItems[$k]=self::_getItem($item);
			}
			return $newItems;
		} else {
			return self::sanitize($items);
		}
    }

	public static function delete($items, $method='request') {
		switch (strtolower($method)) {
			case 'get':
				$src = &$_GET;
				break;
			case 'post':
				$src = &$_POST;
				break;
			default:
				$src = &$_REQUEST;
				break;
		}
        foreach ((array)$items as $item) {
    		if (isset($src[$item])) {
                unset($src[$item]);
    		}
        }
	}

	public static function sanitize($string) {
        if (is_array($string)) {
            $newArray = [];
            foreach ($string as $s) {
                $newArray[] = self::sanitize($s);
            }
            return $newArray;
        } else {
    		return trim(htmlentities($string, ENT_QUOTES, 'UTF-8'));
        }
	}
}
