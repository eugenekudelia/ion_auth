<?php defined('SYSPATH') OR die('No direct script access.');
/**
* Name:  Auth Lang - Russian
*
* Author: Ben Edmunds
* 		  ben.edmunds@gmail.com
*         @benedmunds
*
* Author: Eugene Kudelia
*
* Location: https://github.com/eugenekudelia/ion_auth
*
* Created:  03.09.2013
*
* Description:  Russian language file for Ion Auth example views
*
*/
return array(

	// General
	'Submit'		=> 'Сохранить',
	'Send'			=> 'Отправить',
	'characters'	=> 'знаков',
	'default'		=> 'по умолчанию',
	'Yes'			=> 'Да',
	'No'			=> 'Нет',
	'Display name'	=> 'Отображаемое имя',
	'Required'		=> 'Обязательно',
	'User'			=> 'Пользователь',
	'Profile'		=> 'Профиль',
	'Permissions'	=> 'Права доступа',
	'Groups'		=> 'Группы',
	'Gender'		=> 'Пол',
	'New Account'	=> 'Новый аккаунт',

	'Active'		=> 'Активный',
	'Inactive'		=> 'Неактивный',
	'Activate'		=> 'Активировать',
	'Deactivate'	=> 'Деактивтровать',
	'Delete'		=> 'Удалить',
	'Restricted access'	=> 'Ограниченный доступ',
	'You’re going to delete'	=> 'Вы собитраетесь удалить',
	'account'		=> 'аккаунт',
	''	=> '',
	'This cannot be undone.'	=> 'Это действие нельзя отменить.',
	''	=> '',

	// Errors
	'error_csrf' => 'Запрошенные данные не прошли проверку на безопасность',

	// Password general
	'Password'						=> 'Пароль',
	'Change password'				=> 'Сменить пароль',
	'New password'					=> 'Новый пароль',
	'Confirm password'				=> 'Подтвердите пароль',
	'Password notification method'	=> 'Способ оповещения',
	'Show the password on screen'	=> 'Показать пароль на экране',
	'Send the password by email'	=> 'Отправить по электронной почте',
	'Forgot your password?'			=> 'Забыли пароль?',

	// Login / Register / Edit User / Profile
	'Login'			=> 'Вход',
	'Email'			=> 'Email',
	'hint_email'	=> 'Не отображается публично',
	'Username'		=> 'Имя пользователя',
	'Belongs to Groups:'	=> 'Принадлежность к группам:',
	'hint_display_name'		=> 'Если не указано, будет отображаться Имя пользователя',

	// Login
	'login_explain :identity'	=> 'Для авторизации введите свои :identity и пароль.',
	'Log in'		=> 'Войти',
	'Remember me'	=> 'Запомнить меня',
	'Not registered yet?'					=> 'Нет аккаунта?',
	'Not registered? Join now &#58;&#41;'	=> 'Нет аккаунта? Присоединяйтесь &#58;&#41;',

	// Register
	'Register'		=> 'Регистрация',
	'Registration'	=> 'Регистрация',
	'Join the'		=> 'Присоединяйтесь к',
	'hint_trusted_email'	=> 'Используйте только доверенный Email',
	'Send me my password by email'	=> 'Отправить пароль на мой Email',

	// Create User
	'Create User Account'	=> 'Новый аккаунт',

	// Edit User
	'User account does not exist'	=> 'Аккаунт не найден',
	'You have no permission to edit account: :username'
		=> 'Вы не можете редактировать аккаунт: :username',
	
	'User removed from groups (id): :groups'
		=> 'Пользователь удален из групп (id): :groups',
	'Error while attempt to remove user from groups (id): :groups'
		=> 'Ошибка при попытке удаления пользователя из групп (id): :groups',
	'User added to groups (id): :groups'
		=> 'Пользователь включен в группы (id): :groups',
	'Error while attempt to add user to groups (id): :groups'
		=> 'Ошибка при попытке включения пользователя в группы (id): :groups',
	
	'Edit User Account'	=> 'Редактировать аккаунт',

	'Change Password:' => 'Сменить пароль:',
	'Account statistics:'	=> 'Статистика аккаунта:',
	'Created on'	=> 'Время создания',
	'Last login'	=> 'Последний вход',
	'Login count'	=> 'Всего входов',
	// Edit User - Groups
	'Member of Groups:'	=> 'Участник групп:',
	// Edit User - Permissions
	'Access to resources / actions:'	=> 'Доступ к ресурсам / действиям:',
	'Member of the Administrator group has got full access.'
		=> 'Участник группы Administrator обладает полным доступом.',
	// Edit User - Profile
	'Display Name'	=> 'Отображаемое имя',
	'(Full) Name'	=> '(Полное) имя',
	'hint_full_name'	=> 'Не отображается. Только для контактов и рассылок.',
	'Male'			=> 'Мужской',
	'Female'		=> 'Женский',
	'Not Telling'	=> 'Не указан',
	'Public Email'	=> 'Открытый Email',
	'hint_public_email'	=> 'Будет виден всем',
	'Website'		=> 'Веб-сайт',
	'Date of birth'	=> 'Дата рождения',
	'Locality / Contact info'	=> 'Местоположение / контакты',
	'Additional info'	=> 'Дополнительно',
	'Avatar:'			=> 'Аватар:',
	''	=> '',
	''	=> '',

	// Edit User Profile

	// Change password
	'Change Password' => 'Сменить пароль',
	'Old password' => 'Старый пароль',
	'Confirm new password'	=> 'Подтвердить новый пароль',
	'Show the new password on screen' => 'Показать новый пароль на экране',
	'Send the new password by email' => 'Отправить по электронной почте',
	'Change' => 'Сменить',

	// Forgot Password
	'Forgot Your Password?'	=> 'Забыли пароль?',
	'info_forgot_password'	=> 'Введите адрес эл. почты своего аккаунта, чтобы мы могли отправить вам данные для восстановления пароля.',
	'Account email'				=> 'Email аккаунта',

	// Reset Password
	'Reset Password'	=> 'Восстановить пароль',

	// User list 
	'User List'	=> 'Список пользователей',
	'User filter'	=> 'Фильтры пользователей',
	'No filter'		=> 'Без фильтра',
	'by status'	=> 'статус',
	'by group'	=> 'группа',
	'all managers / users'	=> 'менеджеры / посетители',
	'All managers'	=> 'Все менеджеры',
	'All frontend users'	=> 'Все посетители',
	''	=> '',
	''	=> '',
	''	=> '',
	''	=> '',

	// Groups / Permissions
	'Groups and Permissions'	=> 'Группы и права доступа',
	'Users Groups'	=> 'Группы пользователя',
	'Group Name'	=> 'Название группы',
	'Group'			=> 'Группа',
	'CMS Access'	=> 'Доступ к CMS',
	'The :name group has full access.'	=> 'Группа :name имеет полный доступ.',
	'Restricted access to editing.'		=> 'Restricted access to editing.',
	'No permissions yet.'				=> 'Права доступа не определены.',
	'rename'	=> 'переименовать',
	'Delete Group'	=> 'Удалить группу',
	'Core Group'	=> 'Системная группа',
	'Create Group'	=> 'Создать группу',
	'CMS access'	=> 'Доступ к CMS',
	''	=> '',

	// Email templates
	'Mailer'	=> 'рассылка',
	
	// Activation Email
	'Account Activation'		=> 'Активация аккаунта',
	'Activate user account'		=> 'Активация аккаунта пользователя',
	'Please click this link to'	=> 'Нажмите на ссылку для',
	'activate your account'		=> 'активации вашего аккаунта',
	
	// Forgotten Password Email link to reset
	'Reset password for user'	=> 'Восстановление пароля пользователя',
	'Please click this link to'	=> 'Перейдите по ссылке для',
	'reset your password'		=> 'восстановления вашего пароля.',
	
	// Forgotten Password Email / New Password Email
	'Password for user'	=> 'Пароль для пользователя',
	// Changed
	'Notice of the new password'		=> 'Уведомление о новом пароле',
	'Your password has been changed.'	=> 'Ваш пароль был изменен.',
	// Forgotten
	'Your forgotten password has been reset.'	=> 'Ваш забытый пароль был сброшен.',
	
	// New User Details Email
	'Welcome to :site_name'		=> 'Добро пожаловать на :site_name',
	'Thank you for registering on :site_link. Your account details are:'
		=> 'Спасибо за регистрацию на :site_link. Детали вашего аккаунта:',
	
	// Common for emails
	'Login page'	=> 'Страница авторизации',
	'Reset the forgotten password'	=> 'Восстановление забытого пароля',
	'You can change the password on your profile page when logged in.'
		=> 'Вы можете изменить пароль на странице профиля, войдя в систему.',
);
