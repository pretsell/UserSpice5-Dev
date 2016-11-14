<?php
/*
UserSpice
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
 * master_form.php
 * This script is included by the "main" pages of the site and in turn includes
 * all necessary includes/classes/helpers and finally the form itself (found
 * either in UserSpice/forms or in UserSpice/local/forms)
 *
 * docroot/
 *   +-----UserSpice/
 *   |       +-------forms/
 *   |       |         +---form1.php
 *   |       |         +---form2.php (ignored because form2.php exists in local/)
 *   |       |         +---form3.php
 *   |       |         +---master_form.php - THIS SCRIPT
 *   |       |              (includes includes/classes/helpers and then $formname)
 *   |       +-------local/
 *   |       |         +---forms/
 *   |       |               +---form2.php (customized form)
 *   |       +-------form1.php
 *   |                (accessed via url www.example.com/UserSpice/form1.php)
 *   |                (sets $formname to 'form1.php' and includes master_form.php)
 *   +-----form2.php
 *          (accessed via url www.example.com/form1.php)
 *          (sets $formname to 'form2.php' and includes master_form.php)
 *
 * You can move the UserSpice/x forms anywhere if it works better for your web-site.
 *
 * DO NOT CHANGE THIS SCRIPT. If you wish to customize it, COPY IT TO UserSpice/local/forms/
 * and then modify it. "Main" forms will detect the customized version and use it instead
 * of this (current) script.
 */

# Security - make sure $formName is set with appropriate default
if (!isset($formName)) {
    $formName = basename($_SERVER['PHP_SELF']);
}
# Security - make sure $formName contains a legitimate value AND user is allowed to access this $formName
if (!securePage($formName)) {
    redirect::to(configGet('redirect_deny_noperm', 'index.php'));
    die();
}
if (!isset($disableMasterHeaders)) {
    require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/header.php';
}
if (!isset($disableMasterNavigation)) {
    require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/navigation.php';
}

# Everything seems fine - find the actual form and include it
if ($formPath = pathFinder($formName, ABS_US_ROOT, 'form_search_path', [US_URL_ROOT.'custom/forms/', US_URL_ROOT.'forms/'])) {
    $successes = $errors = []; # some convenient initializations
    require_once $formPath;
} else {
    die("SYSTEM ERROR: Cannot find `$formName`");
}

if (!isset($disableMasterFooters)) {
    require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls
    require_once ABS_US_ROOT.US_URL_ROOT.'users/includes/html_footer.php'; // currently just the closing /body and /html
}
