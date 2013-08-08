<html>
<body>
	<p><?php echo ion__('Activate user account', NULL, 'email').': <strong>'.$identity.'</strong>'; ?></p>
	<p><?php echo ion__('Please click this link to', NULL, 'email').' '.HTML::anchor('activate/'.$id.'/'.$activation_code, ion__('activate your account', NULL, 'email'), NULL, 'http');?>.</p>
</body>
</html>