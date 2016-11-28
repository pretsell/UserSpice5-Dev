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
 // UserSpice Specific Functions
function testUS() {
	echo "<br>";
	echo "UserSpice Functions have been properly included";
	echo "<br>";
}


function get_gravatar($email, $s = 120, $d = 'mm', $r = 'pg', $img = false, $atts = array() ) {
	$url = 'https://www.gravatar.com/avatar/';
	$url .= md5( strtolower( trim( $email ) ) );
	$url .= "?s=$s&d=$d&r=$r";
	if ( $img ) {
		$url = '<img src="' . $url . '"';
		foreach ( $atts as $key => $val )
		$url .= ' ' . $key . '="' . $val . '"';
		$url .= ' />';
	}
	return $url;
}

//Check if the group has admin privileges
function groupIsAdmin($id) {
	$db = DB::getInstance();
	if ($g = $db->queryById('groups', $id)->first()) {
        return ($g->is_admin);
    }
    return false;
}
//Check if a group ID exists in the DB
function groupIdExists($id) {
    return (boolean)fetchGroupDetails($id);
}

//Check if a user ID exists in the DB
function userIdExists($id) {
	$db = DB::getInstance();
	return $db->queryById('users', $id)->found();
}

//Retrieve information for a single group
function fetchGroupDetails($id) {
	$db = DB::getInstance();
	return $db->queryById('groups', $id)->first();
}

//Retrieve all group types
function fetchAllGroupTypes() {
	$db = DB::getInstance();
	return $db->queryAll('grouptypes')->results();
}

//Find the row where $searchProp has $searchVal in the array of
//objects in $aoo (arrayOfObject) and return the property of $rtnProp
//  (this is useful for searching results from database)
function findValInArrayOfObject($aoo, $searchProp, $searchVal, $rtnProp) {
    foreach ($aoo as $o) {
        if ($o->$searchProp == $searchVal) {
            return $o->$rtnProp;
        }
    }
    return false;
}

//Retrieve information for all users
function fetchAllUsers() {
	$db = DB::getInstance();
	return $db->queryAll('users')->results();
}

//Retrieve complete user information by username, token or ID
function fetchUserDetails($username=NULL,$token=NULL, $id=NULL) {
    global $T;
	if ($username!=NULL) {
		$column = "username";
		$data = $username;
	} elseif ($id!=NULL) {
		$column = "id";
		$data = $id;
	}
	$db = DB::getInstance();
	$query = $db->query("SELECT * FROM $T[users] WHERE $column = $data LIMIT 1");
	$results = $query->first();
	return ($results);
}

//Retrieve list of groups a user is member of
function fetchUserGroups($user_id) {
    global $T;
	$db = DB::getInstance();
	$query = $db->query("SELECT * FROM $T[groups_users] WHERE user_id = ?",array($user_id));
	$results = $query->results();
	return ($results);
}


//Retrieve list of users/groups who are members of a given group (NO NESTING)
// or (if $reverse_logic == true) who are NOT members of a given group
function fetchGroupMembers_raw($group_id, $reverse_logic=false) {
    global $T;
	$db = DB::getInstance();
	if ($reverse_logic) {
		$sql = "SELECT users.id as id, username AS name, 'user' AS group_or_user
						FROM $T[users] users
						WHERE NOT EXISTS (
							SELECT *
							FROM $T[groups_users_raw]
							WHERE users.id = user_id
							AND user_is_group = 0
							AND group_id = ?
						)
						UNION
						SELECT groups.id as id, groups.name as name, 'group' AS group_or_user
						FROM $T[groups] groups
						WHERE id != ?
						AND NOT EXISTS (
							SELECT *
							FROM $T[groups_users_raw]
							WHERE groups.id = user_id
							AND user_is_group = 1
							AND group_id = ?
						)
						";
		$bindvals = array($group_id,$group_id,$group_id);
	} else {
		$sql = "SELECT user_id as id, username AS name, 'user' AS group_or_user
						FROM $T[groups_users_raw]
						JOIN $T[users] users ON (user_id = users.id)
						WHERE user_is_group = 0
						AND group_id = ?
						UNION
						SELECT user_id as id, name AS name, 'group' AS group_or_user
						FROM $T[groups_users_raw]
						JOIN $T[groups] groups ON (user_id = groups.id)
						WHERE user_is_group = 1
						AND group_id = ?
						";
		$bindvals = array($group_id,$group_id);
	}
	#echo "DEBUG: group_id = $group_id, sql=$sql<br />\n";
	$query = $db->query($sql,$bindvals);
	#echo "DEBUG: count=".$query->count()."<br />\n";
	return $query->results();
}

//Retrieve list of users who are members of a given group
function fetchUsersByGroup($group_id) {
    global $T;
	$db = DB::getInstance();
	$sql = "SELECT user_id, fname, lname, username
			FROM $T[groups_users]
			JOIN $T[users] users ON (user_id = users.id)
			WHERE group_id = ?";
	#echo "DEBUG: group_id = $group_id, sql=$sql<br />\n";
	return $db->query($sql,array($group_id))->results();
}

// Delete row from grouptypes
function deleteGrouptypes($grouptype_ids, &$errors, &$successes) {
    global $T;
	$db = DB::getInstance();
    $count = 0;
    foreach ((array)$grouptype_ids as $id) {
        if ($db->query("SELECT id FROM $T[groups] WHERE grouptype_id = ?", [$id])->count()) {
            $errors[] = lang('GROUPTYPE_IN_USE');
            $errors[] = lang('GROUPTYPE_DELETE_FAILED');
        } else {
            $db->deleteById('grouptypes', $id);
            if (!$db->count()) {
                $errors[] = lang('GROUPTYPE_DELETE_FAILED');
            }
            $count += $db->count();
        }
    }
    if ($count) {
        $successes[] = lang('GROUPTYPE_DELETE_SUCCESS', $count);
    }
    return $count;
}
//Remove user(s) from group(s)
// $user_is_group is provided programmatically (never from a form) so doesn't
// need to be bound
function deleteGroupsUsers_raw($groups, $users, $user_is_group=0) {
    global $T;
	$db = DB::getInstance();
    $count = 0;
	$sql = "DELETE FROM $T[groups_users_raw]
            WHERE user_is_group = $user_is_group
            AND group_id = ?
			AND user_id = ?";
    foreach ((array)$groups as $g) {
        foreach ((array)$users as $u) {
        	$count += $db->query($sql, [$g, $u])->count();
        }
    }
    return $count;
}

//Retrieve a list of all .php files in root files folder
function getPathPhpFiles($absRoot,$urlRoot,$fullPath) {
	$directory = $absRoot.$urlRoot.$fullPath;
	//bold ($directory);
	$pages = glob($directory . "*.php");

	foreach ($pages as $page) {
		$fixed = str_replace($absRoot.$urlRoot,'',$page);
		$row[$fixed] = $fixed;
	}
	return $row;
}

//Retrieve a list of all .php files in root files folder
function getPageFiles() {
	$directory = "../";
	$pages = glob($directory . "*.php");
	foreach ($pages as $page) {
		$fixed = str_replace('../','/'.US_URL_ROOT,$page);
		$row[$fixed] = $fixed;
	}
	return $row;
}

//Retrive a list of all .php files in users/ folder
function getUSPageFiles() {
	$directory = "../users/";
	$pages = glob($directory . "*.php");
	foreach ($pages as $page) {
		$fixed = str_replace('../users/','/'.US_URL_ROOT.'users/',$page);
		$row[$fixed] = $fixed;
	}
	return $row;
}

//Delete a page from the DB
function deletePages($pages) {
    global $T;
	$db = DB::getInstance();
    $count=0;
    $sql = "DELETE FROM $T[pages] WHERE id = ?";
    foreach ((array)$pages as $p) {
    	if (!$query = $db->query($sql, [$p])) {
    		throw new Exception('There was a problem deleting pages.');
    	} else {
    		$count++;
    	}
    }
    return $count;
}

//Fetch information on all pages
function fetchAllPages() {
    global $T;
	$db = DB::getInstance();
	$query = $db->query("SELECT id, page, private FROM $T[pages] ORDER BY page ASC");
	$pages = $query->results();
	//return $pages;

	if (isset($row)) {
		return ($row);
	} else {
		return $pages;
	}
}

//Fetch information for a specific page
function fetchPageDetails($id) {
    global $T;
	$db = DB::getInstance();
	$query = $db->query("SELECT id, page, private FROM $T[pages] WHERE id = ?",array($id));
	$row = $query->first();
	return $row;
}


//Check if a page ID exists
function pageIdExists($id) {
    global $T;
	$db = DB::getInstance();
	$query = $db->query("SELECT private FROM $T[pages] WHERE id = ? LIMIT 1",array($id));
	$num_returns = $query->count();
	if ($num_returns > 0) {
		return true;
	} else {
		return false;
	}
}

//Toggle private/public setting of a page
function updatePrivate($id, $private) {
    global $T;
	$db = DB::getInstance();
	return $db->update($T['pages'], $id, ['private'=>$private]);
}

//Add a page to the DB
function createPages($pages) {
    global $T;
	$db = DB::getInstance();
	foreach($pages as $page) {
		$fields=array('page'=>$page, 'private'=>'0');
		$db->insert($T['pages'],$fields);
	}
}

//Add authorization for a group to access page(s) (add to groups_pages)
function addGroupsPages($pages, $groups) {
    global $T;
	$db = DB::getInstance();
	$i = 0;
	foreach((array)$groups as $group_id) {
		foreach((array)$pages as $page_id) {
			$query = $db->query(
				"INSERT INTO $T[groups_pages] (group_id, page_id) VALUES (?, ?)",
				array($group_id, $page_id));
			$i++;
		}
	}
	return $i;
}

//Retrieve list of groups that can access a page
function fetchGroupsByPage($page_id) {
    global $T;
	$db = DB::getInstance();
	$query = $db->query("SELECT id, group_id FROM $T[groups_pages] WHERE page_id = ? ",array($page_id));
	return $query->results();
}

//Retrieve list of groups that can access a menu
function fetchGroupsByMenu($menu_id) {
    global $T;
	$db = DB::getInstance();
	$query = $db->query("SELECT id, group_id FROM $T[groups_menus] WHERE menu_id = ? ",array($menu_id));
	return $query->results();
}

//Retrieve list of pages that a group can access
function fetchPagesByGroup($group_id) {
    global $T;
	$db = DB::getInstance();
	$query = $db->query(
        "SELECT gp.id as id, gp.page_id as page_id, p.page as page, p.private as private
         FROM $T[groups_pages] gp
         INNER JOIN $T[pages] p ON (gp.page_id = p.id)
         WHERE gp.group_id = ?",[$group_id]);
	return $query->results();
}

//Remove authorization for group(s) to access page(s) (delete from groups_pages)
function deleteGroupsPages($pages, $groups) {
    global $T;
	$db = DB::getInstance();
	$count = 0;
	$sql = "DELETE FROM $T[groups_pages] WHERE group_id = ? AND page_id = ?";
    foreach ((array)$pages as $p) {
        foreach ((array)$groups as $g) {
        	$q = $db->query($sql, [$p, $g]);
            $count++;
        }
    }
	return $count;
}

//Delete a defined array of users
function deleteUsers($users) {
	$db = DB::getInstance();
	$i = 0;
	foreach((array)$users as $id) {
		$query1 = $db->deleteById('users',$id);
		$query2 = $db->query("DELETE FROM $T[groups_users_raw] WHERE user_id = ?",array($id));
		$query3 = $db->query("DELETE FROM $T[profiles] WHERE user_id = ?",array($id));
		$i++;
	}
	return $i;
}

//Delete rows from groups_roles_users
// optional 2nd arg allows to delete from groups_users_raw if needed
function deleteGroupsRolesUsers($gru_ids, $fix_groups_users_too=false) {
    global $T;
	$db = DB::getInstance();
	$i = 0;
	foreach((array)$gru_ids as $id) {
        if ($fix_groups_users_too) {
            # Check if this is the last group for which this user holds this role
            if ($results = $db->queryById('groups_roles_users', $id)->first()) {
                $sql = "SELECT id
                        FROM $T[groups_roles_users]
                        WHERE role_group_id = ?
                        AND user_id = ?
                        AND id != ?";
                if ($db->query($sql, [$results->role_group_id, $results->user_id, $id])->count() < 1) {
                    deleteGroupsUsers_raw($results->role_group_id, $results->user_id);
                }
            }
        }
		$i += $db->deleteById("groups_roles_users",$id)->count();
	}
	return $i;
}

function defaultPage($type) {
	if ($page = configGet('redirect/default_'.$type.'_page'))
		return $page;
	switch ($type) {
		case 'blocked':
			$page = 'blocked.php';
			break;
		case 'nologin':
		 	$page = configGet('redirect_deny_noperm', 'nologin.php');
			break;
		default:
		 	$page = 'index.php';
			break;
	}
	return $page;
}

//Check token, die if bad
function checkToken($name='csrf', $method='post') {
	if (Input::exists($method)) {
		if (!Token::check(Input::get($name))) {
			die(lang('TOKEN'));
		}
	}
}

// find a file in a (configurable) list of paths
function pathFinder($file, $root=null, $configPathToken=null, $defaultPath=null) {
    if (is_null($root)) {
        $root = US_ROOT_DIR;
    }
    $paths = null;
    if ($configPathToken) {
        $paths = configGet($configPathToken, $defaultPath);
    }
    if (empty($paths)) {
        if (empty($defaultPath)) {
            $paths = configGet('US_SCRIPT_PATH', ['local/', 'us_core/']);
        } else {
            $paths = $defaultPath;
        }
    }
    if (!is_array($paths)) {
        $paths = preg_split('/[:;]/', $paths);
    }
    $root = trim($root);
    #dbg(substr($root, -1, 1));
    if ($root && substr($root, -1, 1) != '/') {
        #dbg("Adding<br />\n");
        $root .= '/';
    }
    foreach ($paths as $p) {
        #dbg("pathFinder($file): Checking '$p'<br />\n");
        $p = trim($p);
        #if (!$p) continue; // use "." if you want cwd
        if ($p && substr($p, -1, 1) != '/') {
            $p .= '/';
        }
        #dbg($root.$p.$file);
        if (file_exists($root.$p.$file)) {
            #dbg("pathFinder($file): Returning ".$root.$p.$file);
            return $root.$p.$file;
        }
    }
    #dbg("pathFinder($file): Failure<br />\n");
    return false;
}

//Check if a user has access to a page
function securePage($uri) {
	global $user, $T;
	/*
	Write the requested $uri to the session variable.
	This will preserve the request for a secured page.
	*/
	$_SESSION['securePageRequest']= $uri;

	# If user is NEVER allowed or ALWAYS allowed then return that status without
	# checking/calculating anything that requires (relatively slow) access to the DB
	// dnd($user);
	if (isset($user) && $user->data() != null) {
		if ($user->data()->permissions==0) {
			Redirect::to(defaultPage('blocked'));
		}
		if ($user->isAdmin())
			return true;
	}

	$db = DB::getInstance();

	# Load site wide settings
	$site_settings_results = $db->query("SELECT * FROM $T[settings]");
	$site_settings = $site_settings_results->first();

    if (substr($uri, 0, strlen(US_URL_ROOT)) == US_URL_ROOT) {
    	$page=substr($uri,strlen(US_URL_ROOT));
    } else {
        $page = $uri;
    }
	//bold($page);

	//retrieve page details
	$query = $db->query("SELECT id, page, private FROM $T[pages] WHERE page = ?",[$page]);
	$count = $query->count();
	if ($count==0) {
        bold($page);
		bold('<br><br>You must go into the Admin Panel and click the Manage Pages button to add this page to the database. Doing so will make this error go away.');
		die();
	}
	$results = $query->first();

	$pageID = $results->id;

	if ($results->private == 0) { //If page is public, allow access
		return true;
	} elseif (!$user->isLoggedIn()) { //If user is not logged in, deny access
		Redirect::to(defaultPage('nologin'));
		return false;
	} elseif (userHasPageAuth($pageID, $user->data()->id)) {
        return true;
    }

	# We've tried everything - send them to the default page
    Redirect::to(US_URL_ROOT.configGet('redirect_deny_noperm'));
    return false;
}

function userHasPageAuth($page_id, $user_id=null) {
	global $user, $T;
	if (is_null($user_id)) {
        $user_id = $user->data()->id;
    }
	$db = DB::getInstance();
	$sql = "SELECT gp.group_id
					FROM $T[groups_pages] gp
					JOIN $T[groups_users] gu ON (gu.group_id = gp.group_id)
					WHERE gu.user_id = ?
					AND gp.page_id = ?";
	return $db->query($sql, [$user_id, $page_id])->count();
}

function checkMenu($menu_id, $user_id=null) {
	global $user, $T;
	$db = DB::getInstance();
	//Grant access if master user
	if ($user->isAdmin())
		return true;

	# Check if this menu has unrestricted access (group_id==0)
	$query = $db->query("SELECT id FROM $T[groups_menus] WHERE group_id = 0 AND menu_id = ?",array($menu_id));
	if ($query->count()) {
		return true;
	}

	# If a user_id was passed in, see if that user is part of a group which has access to this menu item
	if (!is_null($user_id)) {
		$sql = "SELECT gu.group_id
				FROM $T[groups_menus] gm
				INNER JOIN $T[groups_users] gu ON (gm.group_id = gu.group_id)
				WHERE menu_id = ?
				AND user_id = ? ";
		$query = $db->query($sql,array($menu_id, $user_id));
		if ($query->count()) {
			return true;
		}
	}
    return false;
}

//Retrieve information for all groups
function fetchAllGroups() {
	$db = DB::getInstance();
	return $db->queryAll('groups', ['is_role', 'name'])->results();
}

function fetchRolesByType($grouptype_id, $allow_null) {
    global $T;
	$db = DB::getInstance();
    $sql = "SELECT id, name, short_name
            FROM $T[groups] groups
            WHERE is_role > 0
            AND (grouptype_id = ?";
    if ($allow_null) {
        $sql .= " OR grouptype_id IS NULL";
    }
    $sql .= ")";
	return $db->query($sql, [$grouptype_id])->results();
}
function fetchRolesByGroup($group_id) {
    global $T;
	$db = DB::getInstance();
    $sql = "SELECT gru.id, gru.role_group_id, groups.name, groups.short_name,
                    gru.user_id, users.username, users.fname, users.lname
            FROM $T[groups_roles_users] gru
            JOIN $T[groups] groups ON (gru.role_group_id = groups.id)
            JOIN $T[users] users ON (gru.user_id = users.id)
            WHERE gru.group_id = ?";
	return $db->query($sql, [$group_id])->results();
}

//Displays error and success messages
function resultBlock($errors,$successes) {
	//Error block
	if (count($errors) > 0) {
		echo "<div class='alert alert-danger alert-dismissible' role='alert'> <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
		<ul>";
		foreach($errors as $error) {
			echo "<li>".$error."</li>";
		}
		echo "</ul>";
		echo "</div>";
	}

	//Success block
	if (count($successes) > 0) {
		echo "<div class='alert alert-success alert-dismissible' role='alert'> <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
		<ul>";
		foreach($successes as $success) {
			echo "<li>".$success."</li>";
		}
		echo "</ul>";
		echo "</div>";
	}
}

//Inputs language strings from selected language.
function lang($key,$markers = NULL) {
	global $lang;
	$str = isset($lang[$key]) ? $lang[$key] : "";
	if ($markers !== NULL) {
        //Replace any dynamic markers
        $iteration = 1;
		foreach((array)$markers as $marker) {
			$str = str_replace("%m".$iteration."%",$marker,$str);
			$iteration++;
		}
	}
	//Ensure we have something to return
	if ($str == "") {
        #dbg(substr($key, -6));
        if (substr($key, -6) == '_LABEL') {
            return ucwords(strtolower(str_replace(['_LABEL', '_'], ['', ' '], $key)));
        }
		return ("No language key found: $key");
	} else {
		return $str;
	}
}

function addGroupsRolesUsers($group_ids, $role_ids, $user_ids) {
    global $T;
    $db = DB::getInstance();
	$i = 0;
    $findsql = "SELECT id
                FROM $T[groups_roles_users]
                WHERE group_id = ?
                AND role_group_id = ?
                AND user_id = ?";
	$inssql = "INSERT INTO $T[groups_roles_users] (group_id,role_group_id,user_id)
                VALUES (?,?,?)";
	foreach((array)$group_ids as $group_id) {
		foreach((array)$role_ids as $role_id) {
    		foreach((array)$user_ids as $user_id) {
                # Check if it already exists - if not, add it
                $db->query($findsql, [$group_id, $role_id, $user_id]);
                if ($db->count() == 0) {
        			if($db->query($inssql,[$group_id,$role_id,$user_id])) {
        				$i++;
        			}
                }
            }
		}
	}
	return $i;
}

//Add all groups/users to the groups_users_raw mapping table
function addGroupsUsers_raw($group_ids, $user_ids, $user_is_group=0) {
    global $T;
	$db = DB::getInstance();
	$i = 0;
	$sql = "INSERT INTO $T[groups_users_raw] (group_id,user_id,user_is_group) VALUES (?,?,?)";
    $findsql = "SELECT id FROM $T[groups_users_raw] WHERE group_id = ? AND user_id = ? AND user_is_group = ?";
	foreach((array)$group_ids as $group_id){
		foreach((array)$user_ids as $user_id){
			#echo "<pre>DEBUG: AGU: group_id=$group_id, user_id=$user_id</pre><br />\n";
            $db->query($findsql, [$group_id, $user_id, $user_is_group]);
            if ($db->count() == 0) {
    			if($db->query($sql,[$group_id,$user_id,$user_is_group])) {
    				$i++;
    			}
            }
		}
	}
	return $i;
}

//Delete all authorized groups for the given menu(s) and then add from args
function updateGroupsMenus($group_ids, $menu_ids) {
    global $T;
	$db = DB::getInstance();
	$sql = "DELETE FROM $T[groups_menus] WHERE menu_id = ?";
	foreach((array)$menu_ids as $menu_id) {
		#echo "<pre>DEBUG: UGM: group_id=$group_id, menu_id=$menu_id</pre><br />\n";
		$db->query($sql,[$menu_id]);
	}
	return addGroupsMenus($group_ids, $menu_ids);
}

//Add all groups/menus to the groups_menus mapping table
function addGroupsMenus($group_ids, $menu_ids) {
    global $T;
	$db = DB::getInstance();
	$i = 0;
	$sql = "INSERT INTO $T[groups_menus] (group_id,menu_id) VALUES (?,?)";
	foreach((array)$group_ids as $group_id){
		foreach((array)$menu_ids as $menu_id){
			#echo "<pre>DEBUG: AGM: group_id=$group_id, menu_id=$menu_id</pre><br />\n";
			if($db->insert($T['groups_menus'], ['group_id'=>$group_id,'menu_id'=>$menu_id])) {
				$i++;
			}
		}
	}
	return $i;
}


//Delete group(s) from the DB
function deleteGroups($groups) {
	global $errors, $T;
	$i = 0;
	$db = DB::getInstance();
	foreach((array)$groups as $id) {
		if ($id == configGet('newuser_default_group', 1)) {
            $errors[] = lang("CANNOT_DELETE_NEWUSERS");
		} elseif (groupIsAdmin($id)) {
			$errors[] = lang("CANNOT_DELETE_ADMIN");
			$errors[] = lang("HOW_TO_DELETE_ADMIN");
			$errors[] = lang("BE_CAREFUL_DELETE_ADMIN");
		} else {
			$query1 = $db->query("DELETE FROM $T[groups] WHERE id = ?",array($id));
			$query2 = $db->query("DELETE FROM $T[groups_users_raw] WHERE group_id = ?",array($id));
			$query3 = $db->query("DELETE FROM $T[groups_users_raw] WHERE user_id = ? AND user_is_group = 1",array($id));
			$query4 = $db->query("DELETE FROM $T[groups_pages] WHERE group_id = ?",array($id));
			$i++;
		}
	}
	return $i;
}

// include a file and RETURN that value
// This allows the contents of included scripts to be manipulated as strings
function getInclude($fileName) {
    ob_start();
    include($fileName);
    return ob_get_clean();
}

function dbg($str, $level=1) {
    global $debugLevel;
    if (!isset($debugLevel)) $debugLevel = 3;
    if ($level < $debugLevel) {
    	echo "<text padding='1em' align='center'><h4><span style='background:white'>";
        echo "DEBUG: ".$str;
    	echo "</h4></span></text>";
    }
}
