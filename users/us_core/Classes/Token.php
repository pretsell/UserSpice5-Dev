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
class US_Token {
	public static function generate(){
		return Session::put(configGet('session/token_name'), md5(uniqid()));
	}

	public static function check($token){
        #return true; // uncomment to bypass during development
		$tokenName = configGet('session/token_name');
/*
if (Session::exists($tokenName)) {
    dbg("TOKEN Exists ($tokenName)");
} else {
    dbg("TOKEN does NOT exist ($tokenName)");
}
if ($t = Session::get($tokenName)) {
    dbg("Comparing $token to $t");
}
*/
		if (Session::exists($tokenName) && $token === Session::get($tokenName)) {
			Session::delete($tokenName);
			return true;
		}
		return false;
	}
}
