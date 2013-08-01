<html>
<body>
	<p><big><?php echo ion__('email_activate_heading'); ?> <strong style="color: teal;"><?php echo $identity; ?></strong></h3>
	<p><?php echo ion__('email_activate_subheading').' '.HTML::anchor('activate/'.$id.'/'.$activation, ion__('email_activate_link'), NULL, 'http');?></p>
</body>
</html>