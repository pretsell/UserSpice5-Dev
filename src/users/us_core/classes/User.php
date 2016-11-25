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
class US_User
{
    private $_db, $_data, $_sessionName, $_isLoggedIn, $_cookieName;

    public function __construct($user = null)
    {
        $this->_db = DB::getInstance();
        $this->_sessionName = configGet('session/session_name');
        $this->_cookieName = configGet('remember/cookie_name');

        if (!$user) {
            if (Session::exists($this->_sessionName)) {
                $user = Session::get($this->_sessionName);

                if ($this->find($user)) {
                    $this->_isLoggedIn = true;
                } else {
                    //process Logout
                }
            }
        } else {
            $this->find($user);
        }
    }

    public function create($fields = array())
    {
        if (!$this->_db->insert('users', $fields)) {
            throw new Exception('There was a problem creating an account.');
        } else {
            $user_id = $this->_db->lastId();
        }
        $query = $this->_db->insert('groups_users_raw', ['user_id' => $user_id, 'group_id' => 1, 'user_is_group' => 0]);
        // return $user_id;
        $query2 = $this->_db->insert('profiles', ['user_id' => $user_id, 'bio' => 'This is your bio']);

        return $user_id;
    }

    public function find($user = null)
    {
        if ($user) {
            if (is_numeric($user)) {
                $field = 'id';
            } elseif (!filter_var($user, FILTER_VALIDATE_EMAIL) === false) {
                $field = 'email';
            } else {
                $field = 'username';
            }

            $data = $this->_db->get('users', array($field, '=', $user));

            if ($data->count()) {
                $this->_data = $data->first();
                if ($this->data()->account_id == 0 && $this->data()->account_owner == 1) {
                    $this->_data->account_id = $this->_data->id;
                }

                return true;
            }
        }

        return false;
    }

    public function login($username = null, $password = null, $remember = false)
    {
        if (!$username && !$password && $this->exists()) {
            Session::put($this->_sessionName, $this->data()->id);
        } else {
            $user = $this->find($username);
            if ($user) {
                if (password_verify($password, $this->data()->password)) {
                    Session::put($this->_sessionName, $this->data()->id);
                    if ($remember) {
                        $hash = Hash::unique();
                        $hashCheck = $this->_db->get('users_session', array('user_id', '=', $this->data()->id));

                        $this->_db->insert('users_session', array(
                                'user_id' => $this->data()->id,
                                'hash' => $hash,
                                'uagent' => Session::uagent_no_version(),
                            ));

                        Cookie::put($this->_cookieName, $hash, configGet('remember/cookie_expiry'));
                    }
                    $this->_db->query('UPDATE users SET last_login = ?, logins = logins + 1 WHERE id = ?', [date('Y-m-d H:i:s'), $this->data()->id]);

                    return true;
                }
            }
        }

        return false;
    }

    public function loginEmail($email = null, $password = null, $remember = false)
    {
        if (!$email && !$password && $this->exists()) {
            Session::put($this->_sessionName, $this->data()->id);
        } else {
            $user = $this->find($email);

            if ($user) {
                if (password_verify($password, $this->data()->password)) {
                    Session::put($this->_sessionName, $this->data()->id);

                    if ($remember) {
                        $hash = Hash::unique();
                        $hashCheck = $this->_db->get('users_session', array('user_id', '=', $this->data()->id));

                        $this->_db->insert('users_session', array(
                                'user_id' => $this->data()->id,
                                'hash' => $hash,
                                'uagent' => Session::uagent_no_version(),
                            ));

                        Cookie::put($this->_cookieName, $hash, configGet('remember/cookie_expiry'));
                    }
                    $this->_db->query('UPDATE users SET last_login = ?, logins = logins + 1 WHERE id = ?', [date('Y-m-d H:i:s'), $this->data()->id]);

                    return true;
                }
            }
        }

        return false;
    }

    public function exists()
    {
        return (!empty($this->_data)) ? true : false;
    }

    public function id()
    {
        return $this->_data->id;
    }

    public function data()
    {
        return $this->_data;
    }

    public function isLoggedIn()
    {
        return $this->_isLoggedIn;
    }

    // isAdmin()
    // Determine if the current user has admin authorization

    // Note that "magic" IDs (users.id==0 and groups.id==2) which
    // previously identified administrators are no longer supported as of US5.
    // This function - User::isAdmin() - is THE standard way to determine
    // if a user is an admin.
    public function isAdmin()
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        $sql = 'SELECT group_id
			FROM groups_users
			JOIN groups ON (groups_users.group_id = groups.id)
			WHERE groups_users.user_id = ?
			AND groups.admin';
        $this->_db->query($sql, array($this->_data->id));
        if ($this->_db->count() > 0) {
            return true;
        }

        return false;
    }

    public function notLoggedInRedirect($location)
    {
        if ($this->_isLoggedIn) {
            return true;
        } else {
            Redirect::to($location);
        }
    }

    public function logout()
    {
        $this->_db->query('DELETE FROM users_session WHERE user_id = ? AND uagent = ?', array($this->data()->id, Session::uagent_no_version()));

        /*
        At present, user has logged out, so unset session variables and destroy the session.
        Only reason not to destroy session is if other data is being stored in the session even when a user isn't logged in, which doesn't happen at present.
        */
        //session_unset();
        //session_destroy();
        if (isset($_SESSION['gProfileData'])) {
            unset($_SESSION['gProfileData']);
        }
        if (isset($_SESSION['access_token'])) {
            unset($_SESSION['access_token']);
        }
        if (isset($_SESSION['fbProfileData'])) {
            unset($_SESSION['fbProfileData']);
        }
        if (isset($_SESSION['fb_access_token'])) {
            unset($_SESSION['fb_access_token']);
        }
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }

        Session::delete($this->_sessionName);
        Cookie::delete($this->_cookieName);
    }

    public function update($fields = array(), $id = null)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        if (!$this->_db->update('users', $id, $fields)) {
            throw new Exception('There was a problem updating.');
        }
    }

    // hasAuth()
    // Check in groups_pages for the combination of page/auth/group to see
    // if the current user ($this) has this particular authorization
    public function hasAuth($page, $auth=null)
    {
        if ($this->isAdmin()) {
            return true;
        }
        // simple auth = this user is a member of a group which is in groups_pages for this page
        $sql = "SELECT DISTINCT gu.group_id, gp.auth
                FROM groups_users gu
                JOIN groups_pages gp ON (gu.group_id = gp.group_id)
                WHERE gp.user_id = ?
                AND gp.page_id = ? ";
        $bindvals = [$this->id(), $page];
        if ($auth) {
            // gp.auth = NULL means ANY auth on that page
            $sql .= "AND (gp.auth IS NULL OR gp.auth = ?)";
            $bindvals[] = $auth;
        }
        $groups = $this->_db->query($sql, $bindvals);
        if ($groups->count()) {
            return true;
        }
        return false;
    }
}
