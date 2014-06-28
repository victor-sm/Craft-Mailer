Craft-Mailer
============

Send Emails from the CP to custom recipients, specific users or whole usergroups. 

**Note:** This plugin is currently in public beta.

**Supports:**

- Sending mails in batches.
- Using Twig inside the mails body.
- File-Attachments


##Overview:



##Install:

1. Move the `mailer` directory into the craft/plugins/ directory.
2. Go to `Settings -> Plugins` and enable 'Mailer'.
 
You can now change the settings if you need to. Also make sure Crafts mail settings are correct.


##Services (API):

You can make use of the plugins services in your own plugins:

###newMailer():

Starts a MailerTask based on the passed MailerModel.

```php
craft()->mailer_main->newMailer($mailer);
```

The first parameter is the MailerModel.

###newMailerFromUserGroup():

Starts a MailerTask to specific usergroups, based on passed MailerModel

```php
craft()->mailer_main->newMailerFromUserGroup($usergroup_ids, $mailer, $excludeUserIds);
```

The first parameter is an array of all usergroup ids to whom the mail should be send. The second is the MailerModel. The third parameter is optional and allows you to exclude certain users from the mailer.

**Note**: If you also want to include admins, add `admin` to the `$usergroup_ids` array:

```php
$usergroup_ids = array('admin', 1, 4);
```


###newMailerFromUsers():

Starts a MailerTask to specific users, based on passed MailerModel

```php
craft()->mailer_main->newMailerFromUsers($user_ids, $mailer);
```

The first parameter is an array of all user-ids to whom the mail should be send. The second is the MailerModel.

###Mailer_Model:

The MailerModel specifies the content of the Email and to whom the Email should be send:

```php
$mailer = new Mailer_MailerModel();

$mailer->subject      = 'Mail Subject';
$mailer->sender_name  = 'From Name';
$mailer->sender_mail  = 'your_from@mail.com';
$mailer->htmlBody     = 'Email body';
```

`newMailerFromUsers()` and `newMailerFromUserGroup()` set the recipient attribute on their own.
However if you want to use `newMailer()` you have to set it yourself:

```php
$mailer->recipients = array(
	array('to' => 'test1@example.com', 'bcc' => 'test2@example.com', 'cc' => 'test3@example.com'), //Recipients of the 1. mail
	array('to' => 'test4@example.com, test5@example.com'), //Recipients of the 2. mail
	array('to' => 'test6@example.com') //and so on...
);
```

Optionally you can add a file-attachment by adding the attachment attribute, an example:

```php
$mailer->attachment = \CUploadedFile::getInstanceByName('attachment');
```
