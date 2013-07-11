<?php defined('SYSPATH') OR die('No direct script access.');
/**
* Name:  Ion Auth Lang - English
*
* Author: Ben Edmunds
* 		  ben.edmunds@gmail.com
*         @benedmunds
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  03.14.2010
*
* Description:  English language file for Ion Auth messages and errors
*
*/
return array(
	// Account Creation
	'account_creation_successful'			=> 'Account Successfully Created',
	'account_creation_unsuccessful'			=> 'Unable to Create Account',
	'account_creation_duplicate_email'		=> 'Email Already Used or Invalid',
	'account_creation_duplicate_username'	=> 'Username Already Used or Invalid',

	// Password
	'password_change_successful'	=> 'Password Successfully Changed',
	'password_change_unsuccessful'	=> 'Unable to Change Password',
	'forgot_password_successful'	=> 'Password Reset Email Sent',
	'forgot_password_unsuccessful'	=> 'Unable to Reset Password',

	// Activation
	'activate_successful'			=> 'Account Activated',
	'activate_unsuccessful'			=> 'Unable to Activate Account',
	'deactivate_successful'			=> 'Account De-Activated',
	'deactivate_unsuccessful'		=> 'Unable to De-Activate Account',
	'activation_email_successful'	=> 'Activation Email Sent',
	'activation_email_unsuccessful'	=> 'Unable to Send Activation Email',

	// Login / Logout
	'login_successful'				=> 'Logged In Successfully',
	'login_unsuccessful'			=> 'Incorrect Login',
	'login_unsuccessful_not_active'	=> 'Account is inactive',
	'login_timeout'					=> 'Temporarily Locked Out.  Try again later.',
	'logout_successful'				=> 'Logged Out Successfully',

	// Account Changes
	'update_successful'      => 'Account Information Successfully Updated',
	'update_unsuccessful'    => 'Unable to Update Account Information',
	'delete_successful'      => 'User Deleted',
	'delete_unsuccessful'    => 'Unable to Delete User',

	// Groups
	'group_creation_successful'	=> 'Group created Successfully',
	'group_already_exists'		=> 'Group name already taken',
	'group_update_successful'	=> 'Group details updated',
	'group_delete_successful'	=> 'Group deleted',
	'group_delete_unsuccessful'	=> 'Unable to delete group',
	'group_name_required'		=> 'Group name is a required field',

	// Email Subjects
	'email_forgotten_password_subject'	=> 'Forgotten Password Verification',
	'email_new_password_subject' 		=> 'New Password',
	'email_activation_subject'			=> 'Account Activation',
);
