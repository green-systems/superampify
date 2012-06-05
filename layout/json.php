<?php $base = array(
	'subsonic-response' => array(
		'status' => $data['status'],
		'version' => '1.7.0',
		'xmlns' => 'http://subsonic.org/restapi'
	)
);
if (isset($data['callback']))
	header ("Content-Type:text/javascript;charset=UTF-8");
else
	header ("Content-Type: application/json;charset=UTF-8");
	/**
	while we wait for PHP 5.4, just use this
	ugly replace to unescape slashes on url.

	Later, use json_encode($result, JSON_UNESCAPED_SLASHES);
	**/
?>
<?php if (isset($data['callback'])): ?><?php echo $data['callback']; ?>(<?php endif; ?>
<?php echo str_replace('\\/', '/', json_encode($base)); ?>
<?php if (isset($data['callback'])): ?>);<?php endif; ?>