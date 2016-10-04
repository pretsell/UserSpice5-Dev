# UserSpice5-Dev
UserSpice 5 Dev Channel - Note this is PRE Alpha at best.  Not for production use.

The UserSpice 5 channel is intended to permit larger or more significant changes to the code base. There is still a lot of work that needs to be done.

To get started you will need to rename users/db_cred.config.php to users/db_cred.php and fill in the empty values.

You will need to manually import the sql dump located at git location sql/userspice5.sql . This is a "sanitized" dump, which *should* be free of credentials. After the import, you can edit the settings table and fill in the following keys:
site_name (e.g. My Site)
site_url (e.g. https://example.com/)

If you are using recaptcha, then you need to put your keys in here:
recaptcha_private (e.g. google test key is 6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe)
recaptcha_public (e.g. google test key is 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI)

If you are using the Google login, then you will need to enter your id and secret here after obtaining them with these instructions
https://developers.google.com/api-client-library/php/auth/web-app#creatingcred
gid (i.e. this is the Client ID)
gsecret (this is the secret/private key)

If you are using the Facebook login, then you will need to enter your id and secret here:
fbid (i.e. this is the App ID)
fbsecret (this is the secret/private key)
