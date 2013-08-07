<html>
<body>
	<p><?php echo ion__('Password for user').': <strong style="color: teal;">'.$username.'</strong>'; ?></p>
	<p><?php echo ion__('Your forgotten password has been reset.'); ?></p>
	<p><?php echo ion__('New password').': <strong>'.$password.'</strong>'; ?></p>
	<p><?php echo HTML::anchor('login', ion__('Login page'), NULL, 'http'); ?></p>
	<p><?php echo ion__('You can change the password on your profile page when logged in'); ?></p>
</body>
</html>