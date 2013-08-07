<html>
<body>
	<p><?php echo ion__('Activate user account').': <strong style="color: teal;">'.$identity.'</strong>'; ?></p>
	<p><?php echo ion__('Please click this link to').' '.HTML::anchor('activate/'.$id.'/'.$activation_code, ion__('activate your account'), NULL, 'http');?></p>
</body>
</html>