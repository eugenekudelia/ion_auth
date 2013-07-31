<?php defined('SYSPATH') OR die('No direct script access.');
/**
* Name:  Auth Lang - Russian
*
* Author: Ben Edmunds
* 		  ben.edmunds@gmail.com
*         @benedmunds
*
* Author: Daniel Davis
*         @ourmaninjapan
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  03.09.2013
*
* Description:  Russian language file for Ion Auth example views
*
*/
return array(

	//
	'char' => 'знаков',

	// Errors
	'error_csrf' => 'Запрошенные данные не прошли проверку на безопасность',

	// Login
	'login_heading'			=> 'Вход',
	'login_heading_to'		=> 'Вход в',
	'login_subheading'		=> 'Please login with your email/username and password below.',
	'login_email_label'		=> 'Email:',
	'login_username_label'	=> 'Логин:',
	'login_password_label'	=> 'Пароль:',
	'login_remember_label'	=> 'Запомнить меня:',
	'login_submit_btn'		=> 'Войти',
	'login_forgot_password'	=> 'Забыли пароль?',

	// Index
	'index_heading'				=> 'Users',
	'index_subheading'			=> 'Below is a list of the users.',
	'index_fname_th'			=> 'First Name',
	'index_lname_th'			=> 'Last Name',
	'index_email_th'			=> 'Email',
	'index_groups_th'			=> 'Groups',
	'index_status_th'			=> 'Status',
	'index_action_th'			=> 'Action',
	'index_active_link'			=> 'Active',
	'index_inactive_link'		=> 'Inactive',
	'index_create_user_link'	=> 'Create a new user',
	'index_create_group_link'	=> 'Create a new group',

	// Deactivate User
	'deactivate_heading'					=> 'Deactivate User',
	'deactivate_subheading'					=> 'Are you sure you want to deactivate the user \'%s\'',
	'deactivate_confirm_y_label'			=> 'Yes:',
	'deactivate_confirm_n_label'			=> 'No:',
	'deactivate_submit_btn'					=> 'Submit',
	'deactivate_validation_confirm_label'	=> 'confirmation',
	'deactivate_validation_user_id_label'	=> 'user ID',

	// Create User
	'create_user_heading'							=> 'Create User',
	'create_user_subheading'						=> 'Please enter the users information below.',
	'create_user_fname_label'						=> 'First Name:',
	'create_user_lname_label'						=> 'Last Name:',
	'create_user_company_label'						=> 'Company Name:',
	'create_user_email_label'						=> 'Email:',
	'create_user_phone_label'						=> 'Phone:',
	'create_user_password_label'					=> 'Password:',
	'create_user_password_confirm_label'			=> 'Confirm Password:',
	'create_user_submit_btn'						=> 'Create User',
	'create_user_validation_fname_label'			=> 'First Name',
	'create_user_validation_lname_label'			=> 'Last Name',
	'create_user_validation_email_label'			=> 'Email Address',
	'create_user_validation_phone1_label'			=> 'First Part of Phone',
	'create_user_validation_phone2_label'			=> 'Second Part of Phone',
	'create_user_validation_phone3_label'			=> 'Third Part of Phone',
	'create_user_validation_company_label'			=> 'Company Name',
	'create_user_validation_password_label'			=> 'Password',
	'create_user_validation_password_confirm_label'	=> 'Password Confirmation',

	// Edit User
	'edit_user_heading'								=> 'Edit User',
	'edit_user_subheading'							=> 'Please enter the users information below.',
	'edit_user_fname_label'							=> 'First Name:',
	'edit_user_lname_label'							=> 'Last Name:',
	'edit_user_company_label'						=> 'Company Name:',
	'edit_user_email_label'							=> 'Email:',
	'edit_user_phone_label'							=> 'Phone:',
	'edit_user_password_label'						=> 'Password: (if changing password)',
	'edit_user_password_confirm_label'				=> 'Confirm Password: (if changing password)',
	'edit_user_groups_heading'						=> 'Member of groups',
	'edit_user_submit_btn'							=> 'Save User',
	'edit_user_validation_fname_label'				=> 'First Name',
	'edit_user_validation_lname_label'				=> 'Last Name',
	'edit_user_validation_email_label'				=> 'Email Address',
	'edit_user_validation_phone1_label'				=> 'First Part of Phone',
	'edit_user_validation_phone2_label'				=> 'Second Part of Phone',
	'edit_user_validation_phone3_label'				=> 'Third Part of Phone',
	'edit_user_validation_company_label'			=> 'Company Name',
	'edit_user_validation_groups_label'				=> 'Groups',
	'edit_user_validation_password_label'			=> 'Password',
	'edit_user_validation_password_confirm_label'	=> 'Password Confirmation',

	// Create Group
	'create_group_title'					=> 'Create Group',
	'create_group_heading'					=> 'Create Group',
	'create_group_subheading'				=> 'Please enter the group information below.',
	'create_group_name_label'				=> 'Group Name:',
	'create_group_desc_label'				=> 'Description:',
	'create_group_submit_btn'				=> 'Create Group',
	'create_group_validation_name_label'	=> 'Group Name',
	'create_group_validation_desc_label'	=> 'Description',

	// Edit Group
	'edit_group_title'					=> 'Edit Group',
	'edit_group_saved'					=> 'Group Saved',
	'edit_group_heading'				=> 'Edit Group',
	'edit_group_subheading' 			=> 'Please enter the group information below.',
	'edit_group_name_label'				=> 'Group Name:',
	'edit_group_desc_label'				=> 'Description:',
	'edit_group_submit_btn'				=> 'Save Group',
	'edit_group_validation_name_label'	=> 'Group Name',
	'edit_group_validation_desc_label'	=> 'Description',

	// Change Password
	'change_password_heading'								=> 'Change Password',
	'change_password_old_password_label'					=> 'Old Password:',
	'change_password_new_password_label'					=> 'New Password (at least %s characters long):',
	'change_password_new_password_confirm_label'			=> 'Confirm New Password:',
	'change_password_submit_btn'							=> 'Change',
	'change_password_validation_old_password_label'			=> 'Old Password',
	'change_password_validation_new_password_label'			=> 'New Password',
	'change_password_validation_new_password_confirm_label'	=> 'Confirm New Password',

	// Forgot Password
	'forgot_password_heading'					=> 'Забыли пароль?',
	'forgot_password_subheading'				=> 'Please enter your %s so we can send you an email to reset your password.',
	'forgot_password_email_label'				=> '%s:',
	'forgot_password_submit_btn'				=> 'Отправить',
	'forgot_password_validation_email_label'	=> 'Email вашего аккаунта',
	'forgot_password_username_identity_label'	=> 'Username',
	'forgot_password_email_identity_label'		=> 'Email',

	// Reset Password
	'reset_password_heading'								=> 'Обновление пароля',
	'reset_password_new_password_label'						=> 'Новый пароль:',
	'reset_password_new_password_confirm_label'				=> 'Подтвердите пароль:',
	'reset_password_submit_btn'								=> 'Сохранить',
	'reset_password_validation_new_password_label'			=> 'New Password',
	'reset_password_validation_new_password_confirm_label'	=> 'Confirm New Password',

	// Activation Email
	'Activate account for :identity'	=> 'Activate account for :identity',
	'Please click this link to :activate'	=> 'Please click this link to :activate.',
	'Activate Your Account'		=> 'Activate Your Account',
	
	// Forgot Password Email
	'Reset Password for :identity'		=> 'Reset Password for :identity',
	'Please click this link to :reset_password'	=> 'Please click this link to :reset_password.',
	'Reset Your Password'		=> 'Reset Your Password',
	
	// New Password Email
	'New Password for :identity'	=> 'New Password for :identity',
	'Your password has been reset to: :new_password'	=> 'Your password has been reset to: :new_password',
);
