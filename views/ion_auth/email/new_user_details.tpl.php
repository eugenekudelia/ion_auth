<html>
<body>
	<p><?php echo ion__('Thank you for registering on :site_link. Your account details are:', array(':site_link' => HTML::anchor('', $site_name, NULL, 'http')), 'email'); ?></p>
	<p><?php echo ion__('Username').': <strong>'.$username.'</strong>'; ?>
	<br /><?php echo ion__('Password').': <strong>'.$password.'</strong>'; ?>
	</p>
	<p><?php echo HTML::anchor('login', ion__('Login page', NULL, 'email'), NULL, 'http'); ?></p>
	<p><?php echo ion__('You can change the password on your profile page when logged in.', NULL, 'email'); ?></p>
</body>
</html>