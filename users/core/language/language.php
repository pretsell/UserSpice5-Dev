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

/*
%m1% - Dymamic markers which are replaced at run time by the relevant index.
*/

//actual logic will eventually go here
$curLangCode = configGet('site_language', 'en');
$langFiles = configGet('language_files');
if (isset($langFiles[$curLangCode])) {
    $curLang = $langFiles[$curLangCode];
} else {
    throw new Exception("ERROR: Language code `$curLangCode` is not configured in `language_files` in local/config.php");
}
if ($curLang != 'english.php') {
    # these values will be over-ridden by the language settings below, but since
    # english is likely to be the most complete language file we will start with
    # this as a base.
    require_once US_ROOT_DIR."core/language/english.php";
    if (file_exists($locallang = US_ROOT_DIR."local/language/english.php")) {
        require_once $locallang;
    }
}
require_once US_ROOT_DIR."core/language/$curLang";
if (file_exists($locallang = US_ROOT_DIR."local/language/$curLang")) {
    require_once $locallang;
}
if (file_exists($devlang = configGet('alt_dev_path')."/language/$curLang")) {
    require_once $devlang;
}
$db = DB::getInstance();
$rows = $db->query("SELECT token, message FROM $T[lang] WHERE lang = ?", [$curLangCode])->results();
foreach ($rows as $row) {
    $lang[$row->token] = $row->message;
}
