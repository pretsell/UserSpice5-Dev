<?php

abstract class StateResponse {
    protected $state=null;
    protected $dflt_redirect = 'index.php';
    protected $do_redirect = true;
    protected $do_logging = true;
    public function respond($do_redirect=true) {
        #dbg("StateResponse->respond: Entering<br />\n");
        if ($this->do_logging) {
            global $user;
            $db = DB::getInstance();
            if ($user && is_object($user->data())) {
                $user_id = $user->data()->id;
            } else {
                $user_id = null;
            }
            $fields = [
                'user_id' => $user_id,
                'page' => $_SERVER['PHP_SELF'],
                'state' => $this->state,
                'ip' => ipCheck(),
            ];
            #dbg("respond: Logging <pre>".print_r($fields,true)."</pre><br />\n")    ;
            $db->insert('audit', $fields);
            if ($db->error()) {
                dbg("DB ERRORS!".$db->errorString());
            }
        }

        if ($this->do_redirect && $do_redirect) {
            $redirect_destination = configGet('redirect_'.$this->state, pathFinder($this->dflt_redirect));
            #echo "REdirect_dest=$redirect_destination<br />\n";
            #echo "state=".$this->state."<br />\n";
            #echo "uur=".US_URL_ROOT."<br />\n";
            #exit;
            Redirect::to(US_URL_ROOT.$redirect_destination);
        }
    }
}

class US_StateResponse_PreLogout extends StateResponse {
    protected $state = 'prelogout';
    protected $do_logging = false; // no need to log before logging out - we will have the logout log
    protected $do_redirect = false; // don't redirect (there isn't even a setting for this)
}

class US_StateResponse_Logout extends StateResponse {
    protected $state = 'logout';
    #protected $do_redirect = false; // DEBUG
    #protected $dflt_redirect = 'index.php';
}

class US_StateResponse_Login extends StateResponse {
    protected $state = 'login';
    #protected $dflt_redirect = 'index.php';
    public function respond($do_redirect=true) {
        parent::respond(false); // don't redirect
        # If the user tried to go to a given page (other than login.php) and redirect_referrer_login
        # is turned on, then now that we are logged in go to that page.
    	if (@$_SESSION['securePageRequest'] && basename($_SESSION['securePageRequest']) != 'login.php' && configGet('redirect_referrer_login')) {
    		$securePageRequest=$_SESSION['securePageRequest'];
    		unset($_SESSION['securePageRequest']);
    		Redirect::to($securePageRequest);
    	} else {
            # Modify the post-login redirect destination by modifying redirect_login in the
            # `settings` database table
            if ($this->do_redirect && $do_redirect) {
                # Modify the post-login redirect destination by modifying redirect_login in the
                # `settings` database table (admin_settings.php)
        		Redirect::to(configGet('redirect_login'));
            }
    	}
    }
}

class US_StateResponse_Blocked extends StateResponse {
    protected $state = 'blocked';
    protected $dflt_redirect = 'blocked.php';
}

class US_StateResponse_DenyNoLogin extends StateResponse {
    protected $state = 'deny_nologin';
    protected $dflt_redirect = 'login.php';
}

class US_StateResponse_DenyNoPerm extends StateResponse {
    protected $state = 'deny_noperm';
    #protected $dflt_redirect = 'index.php';
}

class US_StateResponse_SiteOffline extends StateResponse {
    protected $state = 'site_offline';
    #protected $dflt_redirect = 'index.php';
}
