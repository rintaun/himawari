<?php
$TEMPLATE = 'kyrie';

$base = preg_replace('!/style.php$!', '', preg_replace("!^{$_SERVER['DOCUMENT_ROOT']}!", '', $_SERVER['SCRIPT_FILENAME']));

if (!$url = preg_replace("!^{$base}/style/!", "{$base}/tpl/{$TEMPLATE}/", $_SERVER['REQUEST_URI']))
{
	header('Location: /');
	exit;
}
header("Location: {$url}");
exit;
