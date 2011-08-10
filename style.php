<?php
require_once('config.inc.php');

if (!$url = preg_replace("!{$_ENV['BASE_URL']}style/!i", "{$_ENV['BASE_URL']}tpl/{$_ENV['TEMPLATE']}/", $_ENV['REQUEST']))
{
	header('HTTP/1.0 404 Not Found');
	header("Status: 404 Not Found");
	exit;
}
header("Location: {$url}");
exit;
