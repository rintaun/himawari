<?php
$TEMPLATE = 'kyrie';

if (!$url = preg_replace('!^/style/!', "/tpl/{$TEMPLATE}/", $_SERVER['REQUEST_URI']))
{
	header('Location: /');
	exit;
}
header("Location: {$url}");