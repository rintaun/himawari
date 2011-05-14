<?php
if (!file_exists('../.config.php')) 
{
	header('Location: install.php');
	exit;
}

require_once('../.config.php');

require_once('../lib/Savant3.php');
$tpl = new Savant3();


$TEMPLATE = 'kyrie';

$tpldir = '../tpl/' . $TEMPLATE . '/';

$tpl->addPath('template', $tpldir);

$tpl->tpldir = $tpldir;

$tpl->display('adm/index.tpl.php');
