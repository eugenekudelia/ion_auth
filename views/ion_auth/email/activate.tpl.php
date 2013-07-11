<html>
<body>
	<p><big><?php echo ion__('Activate account for user:'); ?> <strong style="color: teal;"><?php echo $identity; ?></strong></h3>
	<p><?php echo ion__('Please click this link to').' '.HTML::anchor('auth/activate/'.$id.'/'.$activation, ion__('activate your account'), NULL, 'http');?></p>
</body>
</html>