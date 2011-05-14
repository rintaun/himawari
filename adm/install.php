<?php
if (file_exists('../.config.php'))
{
	header('Location: index.php');
	exit;
}

if ((isset($_POST['action'])) && ($_POST['action'] == "execute"))
{
	$queries[] = "
		CREATE TABLE IF NOT EXIST config (
			option	VARCHAR	(32)	NOT NULL	PRIMARY KEY			,
			value	TEXT		NOT NULL
		) CHARSET=UTF-8;
	";

	$queries[] = "
		CREATE TABLE IF NOT EXIST songs (
			id	INT		NOT NULL	PRIMARY KEY	AUTO_INCREMENT	,
			artist	VARCHAR	(64)	NOT NULL					,
			title	VARCHAR	(128)	NOT NULL					,
			desc	TEXT		NOT NULL					,
			fname	VARCHAR	(128)	NOT NULL
		) CHARSET=UTF-8;
	";


	/***********************
	 * config options
	 * - username
	 * - password
	 * - site name
	 * - introduction text
	 * - about me text
	 * - theme
	 ***********************/

	$file = 'install-complete.tpl.php';
	exit;
}
else $file = 'install.tpl.php';

require_once('../lib/Savant3.php');
$tpl = new Savant3();

$TEMPLATE = 'kyrie';

$tpldir = '../tpl/' . $TEMPLATE . '/';

$tpl->addPath('template', $tpldir);
$tpl->tpldir = $tpldir;

$tpl->display('adm/' . $file);
