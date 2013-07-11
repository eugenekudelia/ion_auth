<html>
<body>
	<p><big><?php echo ion__('Password for new user:'); ?> <strong style="color: teal;"><?php echo $identity; ?></strong></big></p>
	<p><?php echo ion__('Welcome to :site_name, :identity!', array(':site_name' => $site_name, ':identity' => $identity)); ?></p>
	<p><?php echo ion__('Password to your account is:'); ?> <strong><?php echo $new_password; ?></strong></p>
	<p><?php echo ion__('You can change the password on your profile page when logged into your account'); ?></p>
	<!-- Вы можете изменить пароль на странице профиля, войдя в свой аккаунт -->
</body>
</html>