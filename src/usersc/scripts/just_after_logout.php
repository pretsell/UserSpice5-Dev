<?php
//This is what happens after a user logs out. Where do you want to send them?  What do you want to do?

Redirect::to(US_URL_ROOT.$site_settings->redirect_logout);
?>