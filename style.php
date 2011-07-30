<?php
$TEMPLATE = 'kyrie';

$base = preg_replace('!/style.php$!i', '', preg_replace("!^{$_SERVER['DOCUMENT_ROOT']}!i", '', $_SERVER['SCRIPT_FILENAME']));

if (!$url = preg_replace("!^{$base}/style/!i", "{$base}/tpl/{$TEMPLATE}/", $_SERVER['REQUEST_URI']))
{
	header('Location: /');
	exit;
}
header("Location: {$url}");
exit;
