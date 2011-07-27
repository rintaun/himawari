<?php
if (!file_exists('../dat/.db'))
{
        header('Location: install.php');
        exit;
}

session_start();

if ((isset($_SESSION['loggedin'])) && ($_SESSION['loggedin'] === true))
{
	header('Location: index.php');
	exit;
}

$db = sqlite_open('../dat/.db');

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

$TEMPLATE = 'kyrie';
$tpldir = '../tpl/' . $TEMPLATE . '/';

$tpl->addPath('template', $tpldir);
$tpl->tpldir = $tpldir;

$tpl->title = (isset($config['sitename'])) ? $config['sitename'] : '';
$tpl->error = $error;

$tpl->display('adm/login.tpl.php');
