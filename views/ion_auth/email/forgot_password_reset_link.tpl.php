<html>
<body>
	<p><?php echo ion__('Reset password for user', NULL, 'email').': <strong>'.$identity.'</strong>'; ?></p>
	<p><?php echo ion__('Please click this link to', NULL, 'email').' '.HTML::anchor($cms.'reset_password/'.$forgotten_password_code, ion__('reset your password', NULL, 'email')); ?></p>
</body>
</html>