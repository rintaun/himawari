<?php
$_CONFIG['DB_POPULATE'] = true;
$_CONFIG['BASE_MOD'] = '..';
require_once('../config.inc.php');

if (!$_ENV['INSTALLED']) header('Location: install.php') and exit;
else if ($_ENV['LOGGED_IN']) header('Location: index.php') and exit;

$error = false;
if (isset($_POST['action']))
{	
	if (($_POST['username'] == $_ENV['DB_DATA']['config']['username'])	&&
		(sha1(md5($_POST['password'])) == $_ENV['DB_DATA']['config']['password']))
	{
		$_SESSION['loggedin'] = true;
		header('Location: index.php');
		exit;
	}
	else $error = true;
}

$tpl->title = (isset($_ENV['DB_DATA']['config']['sitename'])) ? $_ENV['DB_DATA']['config']['sitename'] : '';
$tpl->error = $error;

$tpl->display('adm/login.tpl.php');
