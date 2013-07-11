<html>
<body>
	<p><big><?php echo ion__('Reset password for user:'); ?> <strong style="color: teal;"><?php echo $identity; ?></strong></big></p>
	<p><?php echo ion__('Please click this link to').' '.HTML::anchor('cms/reset_password/'.$forgotten_password_code, ion__('reset your password'), NULL, 'http'); ?></p>
</body>
</html>