<?php
$_CONFIG['BASE_MOD'] = '..';
$_CONFIG['DB_POPULATE'] = true;
require_once('../config.inc.php');

if (!$_ENV['INSTALLED']) header('Location: install.php') and exit;
else if (!$_ENV['LOGGED_IN']) header('Location: login.php') and exit;

$tpl->maxUpload = min(toBytes(ini_get('upload_max_filesize')), toBytes(ini_get('post_max_size')));

$tpl->songlist = $_ENV['DB_DATA']['songs'];
$tpl->config = $_ENV['DB_DATA']['config'];
$tpl->links = $_ENV['DB_DATA']['links'];

$tpl->title = (isset($_ENV['DB_DATA']['config']['sitename'])) ? $_ENV['DB_DATA']['config']['sitename'] : '';
$tpl->introduction = (isset($_ENV['DB_DATA']['config']['introduction'])) ? $_ENV['DB_DATA']['config']['introduction'] : '';
$tpl->about = (isset($_ENV['DB_DATA']['config']['aboutme'])) ? $_ENV['DB_DATA']['config']['aboutme'] : '';

$tpl->display('adm/index.tpl.php');
