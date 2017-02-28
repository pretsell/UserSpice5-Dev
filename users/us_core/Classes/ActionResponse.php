<?php

abstract class ActionResponse {
    protected $token=null;
    protected function Action() {
        $redirect_destination = configGet('redirect_'.$this->token);
        Redirect::to(US_URL_ROOT.$redirect_destination);
    }
}

class US_ActionResponse_Logout extends ActionResponse {
    protected $token = 'logout';
}

class US_ActionResponse_Login extends ActionResponse {
    protected $token = 'login';
}

class US_ActionResponse_Blocked extends ActionResponse {
    protected $token = 'blocked';
}

class US_ActionResponse_Nologin extends ActionResponse {
    protected $token = 'nologin';
}

class US_ActionResponse_Deny_NoPerm extends ActionResponse {
    protected $token = 'deny_noperm';
}
