<?php defined('SYSPATH') or die('No direct script access.');

/*
| -------------------------------------------------------------------------
| Database Type
| -------------------------------------------------------------------------
| If set to TRUE, Ion Auth will use MongoDB as its database backend.
|
| If you use MongoDB there are two external dependencies that have to be
| integrated with your project:
|   CodeIgniter MongoDB Active Record Library - http://github.com/alexbilbie/codeigniter-mongodb-library/tree/v2
|   CodeIgniter MongoDB Session Library - http://github.com/sepehr/ci-mongodb-session
*/
//$config['use_mongodb'] = FALSE;

/*
| -------------------------------------------------------------------------
| MongoDB Collection.
| -------------------------------------------------------------------------
| Setup the mongodb docs using the following command:
| $ mongorestore sql/mongo
|
*/
//$config['collections']['users']          = 'users';
//$config['collections']['groups']         = 'groups';
//$config['collections']['login_attempts'] = 'login_attempts';

return array(

	/*
	| -------------------------------------------------------------------------
	| Session type
	| -------------------------------------------------------------------------
	*/
	'session_type'	=> Session::$default,

	/*
	| -------------------------------------------------------------------------
	| Tables.
	| -------------------------------------------------------------------------
	| Database table names.
	*/
	'tables'	=> array(
		'users'				=> 'users',
		'groups'			=> 'groups',
		'users_groups'		=> 'users_groups',
		'login_attempts'	=> 'login_attempts'
	),

	/*
	| -------------------------------------------------------------------------
	| CMS access column in a table 'groups'
	| -------------------------------------------------------------------------
	*/
	'cms_access'	=> 'cms',

	/*
	 | Users table column and Group table column you want to join WITH.
	 |
	 | Joins from users.id
	 | Joins from groups.id
	 */
	'join'	=> array(
		'users'		=> 'user_id',
		'groups'	=> 'group_id'
	),

	/*
	| -------------------------------------------------------------------------
	| User groups names for User Filter
	| -------------------------------------------------------------------------
	| 
	*/
	'all_managers'			=> 'all-managers',
	'all_managers_name'		=> 'All managers',
	'web_users_name'		=> 'All frontend users',

	/*
	 | -------------------------------------------------------------------------
	 | Hash Method (sha1 or bcrypt)
	 | -------------------------------------------------------------------------
	 | Bcrypt is available in PHP 5.3+
	 |
	 | IMPORTANT: Based on the recommendation by many professionals, it is highly recommended to use
	 | bcrypt instead of sha1.
	 |
	 | NOTE: If you use bcrypt you will need to increase your password column character limit to (80)
	 |
	 | Below there is "default_rounds" setting.  This defines how strong the encryption will be,
	 | but remember the more rounds you set the longer it will take to hash (CPU usage) So adjust
	 | this based on your server hardware.
	 |
	 | If you are using Bcrypt the Admin password field also needs to be changed in order login as admin:
	 | $2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36
	 |
	 | Becareful how high you set max_rounds, I would do your own testing on how long it takes
	 | to encrypt with x rounds.
	 */
	'hash_method'		=> 'sha1',	// IMPORTANT: Make sure this is set to either sha1 or bcrypt
	'default_rounds'	=> 8,		// This does not apply if random_rounds is set to true
	'random_rounds'		=> FALSE,
	'min_rounds'		=> 5,
	'max_rounds'		=> 9,

	/*
	 | -------------------------------------------------------------------------
	 | Authentication options.
	 | -------------------------------------------------------------------------
	 | maximum_login_attempts: This maximum is not enforced by the library, but is
	 | used by $this->ion_auth->is_max_login_attempts_exceeded().
	 | The controller should check this function and act
	 | appropriately. If this variable set to 0, there is no maximum.
	 */
	'site_title' => SITE_NAME ?: Get::setting('site_name'),	// Site Title, example.com
	'admin_email'               => "admin@example.com",	// Admin Email, admin@example.com
	'default_admin'             => 'admin',				// Default administrators group, use name
	'admin_group'               => 'manager',			// Administrators group, use name
	'default_group'             => 'members',			// Default group, use name
	'identity'                  => 'email',				// A database column which is used to login with
	'keep_username'				=> TRUE,				// Protect username from changing
	'min_username_length'       => 3,					// Minimum Required Length of Username
	'max_username_length'       => 24,					// Maximum Allowed Length of Username
	'min_password_length'       => 8,					// Minimum Required Length of Password
	'max_password_length'       => 20,					// Maximum Allowed Length of Password
	'old_password_required'		=> FALSE,				// Old password required when change password
	'email_activation'          => FALSE,				// Email Activation for registration
	'manual_activation'         => FALSE,				// Manual Activation for registration
	'remember_users'            => TRUE, 				// Allow users to be remembered and enable auto-login
	'user_expire'               => (int) 121E4,			// (121E4 ~= 2 weeks) How long to remember the user (seconds). Set to zero for no expiration
	'user_extend_on_login'      => FALSE,				// Extend the users cookies everytime they auto-login
	'track_login_attempts'      => TRUE,				// Track the number of failed login attempts for each user or ip.
	'maximum_login_attempts'    => 3,					// The maximum number of failed login attempts.
	'lockout_time'              => 600,					// The number of miliseconds to lockout an account due to exceeded attempts
	'forgot_password_expiration'=> 0,					// The number of miliseconds after which a forgot password request will expire. If set to 0, forgot password requests will not expire.

	/*
	 | -------------------------------------------------------------------------
	 | Email options.
	 | -------------------------------------------------------------------------
	 | email_config:
	 | 	  'file' = Use the default nApp config or use from a config file
	 | 	  array  = Manually set your email config settings
	 */
	'use_builtin_email'	=> TRUE,	// Send Email using the builtin nApp email class, if false it will return the code and the identity
	'email_config'	=> array(
		'mailtype'		=> 'html',
	),

	/*
	 | -------------------------------------------------------------------------
	 | Use forgot_password_complete() to generate password?
	 | -------------------------------------------------------------------------
	 | Default: activate.tpl.php
	 */
	'public_forgot_password_generate' => FALSE,
	'cms_forgot_password_generate' => FALSE,

	/*
	 | -------------------------------------------------------------------------
	 | Email templates.
	 | -------------------------------------------------------------------------
	 | Folder where email templates are stored.
	 | Default: auth/
	 */
	'email_templates'	=> 'email/',

	/*
	 | -------------------------------------------------------------------------
	 | Activate Account Email Template
	 | -------------------------------------------------------------------------
	 | Default: activate.tpl.php
	 */
	'email_activate'	=> 'activate.tpl',

	/*
	 | -------------------------------------------------------------------------
	 | Forgot Password Email Template
	 | -------------------------------------------------------------------------
	 | Default: forgot_password.tpl.php
	 */
	'email_forgot_password_new'	=> 'forgot_password_new.tpl',

	/*
	 | -------------------------------------------------------------------------
	 | Forgot Password Complete Email Template
	 | -------------------------------------------------------------------------
	 | Default: new_password.tpl.php
	 */
	'email_forgot_password_complete'	=> 'forgot_password_reset_link.tpl',

	/*
	 | -------------------------------------------------------------------------
	 | Change Password New Password Email Template
     | -------------------------------------------------------------------------
     | Default : new_password.tpl.php
	 */
	'email_new_password'	=> 'new_password.tpl',

	/*
	 | -------------------------------------------------------------------------
	 | New User Password Email Template
     | -------------------------------------------------------------------------
     | Default : new_user_password.tpl.php
	 */
	'email_new_user_password'	=> 'new_user_password.tpl',

	/*
	 | -------------------------------------------------------------------------
	 | Salt options
	 | -------------------------------------------------------------------------
	 | salt_length Default: 10
	 |
	 | store_salt: Should the salt be stored in the database?
	 | This will change your password encryption algorithm,
	 | default password, 'password', changes to
	 | fbaa5e216d163a02ae630ab1a43372635dd374c0 with default salt.
	 */
	'salt_length'	=> 10,
	'store_salt'	=> FALSE,

	/*
	 | -------------------------------------------------------------------------
	 | Message Delimiters.
	 | -------------------------------------------------------------------------
	 */
	'message_start_delimiter'	=> '<p>',	// Message start delimiter
	'message_end_delimiter'		=> '</p>',	// Message end delimiter
	'error_start_delimiter'		=> '<p>',	// Error mesage start delimiter
	'error_end_delimiter'		=> '</p>',	// Error mesage end delimiter
);
