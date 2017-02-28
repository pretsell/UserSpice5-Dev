<?php

class ActionResponse_Logout extends US_ActionResponse_Logout {
    protected $token = 'logout';
}

class ActionResponse_Login extends US_ActionResponse_Login {
    protected $token = 'login';
}

class ActionResponse_Blocked extends US_ActionResponse_Blocked {
    protected $token = 'blocked';
}

class ActionResponse_NoLogin extends US_ActionResponse_NoLogin {
    protected $token = 'nologin';
}

class ActionResponse_DenyNoPerm extends US_ActionResponseDenyNoPerm {
    protected $token = 'deny_noperm';
}
