<?php
$_CONFIG['BASE_MOD'] = '..';
require_once('../config.inc.php');

echo "<pre>" . print_r($_ENV,1); exit;

if (!$_ENV['INSTALLED'])
{
        header('Location: install.php');
        exit;
}
else if ($_ENV['LOGGED_IN'])
{
	header('Location: index.php');
	exit;
}

$query = "SELECT * FROM config";
$result = sqlite_query($db, $query);

while ($row = sqlite_fetch_array($result))
{
	$config[$row['opt']] = $row['value'];
}

$error = false;
if (isset($_POST['action']))
{	
	if (($_POST['username'] == $config['username'])	&&
		(sha1(md5($_POST['password'])) == $config['password']))
	{
		$_SESSION['loggedin'] = true;
		header('Location: index.php');
		exit;
	}
	else $error = true;
}

require_once('../lib/Savant3.php');
$tpl = new Savant3();
$tpl->addPath('template', '../tpl/' . $_ENV['TEMPLATE'] . '/');

$tpl->title = (isset($config['sitename'])) ? $config['sitename'] : '';
$tpl->error = $error;

$tpl->display('adm/login.tpl.php');
